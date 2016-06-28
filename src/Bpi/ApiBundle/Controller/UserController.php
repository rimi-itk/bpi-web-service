<?php
/**
 * @file
 *  User controller class
 */
namespace Bpi\ApiBundle\Controller;

use Bpi\ApiBundle\Domain\ValueObject\Subscription;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\Rest\Util\Codes;

use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\Entity\History;
use Bpi\ApiBundle\Domain\Entity\User;
use Bpi\RestMediaTypeBundle\Element\Facet;
use Bpi\RestMediaTypeBundle\Element\FacetTerm;

/**
 * Class UserController
 * @package Bpi\ApiBundle\Controller
 *
 * Rest controller for Users
 */
class UserController extends BPIController
{
    /**
     * List all users
     *
     * @Rest\Get("/")
     * @Rest\View()
     * @return Response
     */
    public function listUsersAction()
    {
        $query = new \Bpi\ApiBundle\Domain\Entity\UserQuery();
        $query->amount(20);
        if (false !== ($amount = $this->getRequest()->query->get('amount', false))) {
            $query->amount($amount);
        }

        if (false !== ($offset = $this->getRequest()->query->get('offset', false))) {
            $query->offset($offset);
        }

        if (false !== ($search = $this->getRequest()->query->get('search', false))) {
            $query->search($search);
        }

        $filters = array();
        $logicalOperator = '';
        if (false !== ($filter = $this->getRequest()->query->get('filter', false))) {
            foreach ($filter as $field => $value) {
                if ($field == 'agency_id' && is_array($value)) {
                    foreach ($value as $val) {
                        if (empty($val)) {continue; }
                        $filters['agency_id'][] = $val;
                    }
                }
            }
            if (isset($filter['logicalOperator']) && !empty($filter['logicalOperator'])) {
                $logicalOperator = $filter['logicalOperator'];
            }
        }

        $facetRepository = $this->getRepository('BpiApiBundle:Entity\UserFacet');
        $facets = $facetRepository->getFacetsByRequest($filters, $logicalOperator);
        $query->filter($facets->userIds);

        if (false !== ($sort = $this->getRequest()->query->get('sort', false))) {
            foreach ($sort as $field => $order) {
                $query->sort($field, $order);
            }
        }

        $users = $this->getRepository('BpiApiBundle:Entity\User')->findByQuery($query);

        if (null === $users) {
            throw new HttpException(Codes::HTTP_NOT_FOUND, 'No users found.');
        }

        $response = $this->get('bpi.presentation.users');
        $response->setTotal($query->total);
        $response->setOffset($query->offset);
        $response->setAmount($query->amount);

        foreach ($facets->facets as $name => $facet) {
            $theFacet = new Facet(Facet::TYPE_STRING, $name);
            foreach ($facet as $key => $term) {
                $value = '';
                $title = null;
                if ($name == 'agency_id') {
                    $value = $term['count'];
                    $title = $term['agencyName'];
                } elseif (isset($term['count'])) {
                    $value = $term['count'];
                    $title = isset($term['title']) ? $term['title'] : null;
                } else {
                    $value = $term;
                }

                $term = new FacetTerm($key, $value, $title);
                $theFacet->addTerm($term);
            }

            $response->addFacet($theFacet);
        }

        foreach ($users as $user) {
            $response->addUser($user);
        }

        return $response;
    }

    /**
     * Returns user by it's id.
     *
     * @param string $userId the user id in database.
     *
     * @Rest\Get("/{userId}")
     * @Rest\View()
     *
     * @return Presentation $document
     */
    public function getUserByIdAction($userId)
    {
        $userRepository = $this->getRepository('BpiApiBundle:Entity\User');

        $user = $userRepository->find($userId);

        if (null === $user) {
            throw new HttpException(Codes::HTTP_NOT_FOUND, "User with id = '{$userId}' not found.");
        }

        $response = $this->get('bpi.presentation.users');
        $response->addUser($user);

        return $response;

        $transform = $this->get('bpi.presentation.transformer');
        $transform->setDoc($this->get('bpi.presentation.users'));
        $document = $transform->transform($user);

        return $document;
    }

    /**
     * Create new user
     *
     * @Rest\Post("/")
     * @Rest\View()
     */
    public function createUserAction()
    {
        $dm = $this->getDoctrineManager();
        $userRepository = $this->getRepository('BpiApiBundle:Entity\User');
        $params = $this->getAllRequestParameters();
        // Strip all params.
        $this->stripParams($params);

        $requiredParams = array(
            'externalId' => 0,
            'email' => 0,
        );
        $this->checkParams($params, $requiredParams);

        foreach ($requiredParams as $param => $count) {
            if ($count == 0) {
                throw new HttpException(Codes::HTTP_BAD_REQUEST, "Param '{$param}' is required.");
            }
        }

        if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
            throw new HttpException(Codes::HTTP_BAD_REQUEST, "Email '{$params['email']}' not valid.");
        }

        // Get agency.
        $agency = $this->getAgencyFromHeader();
        $user = $userRepository->findByExternalIdAgency($params['externalId'], $agency->getId());
        if ($user) {
            throw new HttpException(Codes::HTTP_BAD_REQUEST, "User with externalId = '{$params['externalId']}' and agency = '{$agency->getId()}' already exits.");
        }

        $user = $userRepository->findOneByEmail($params['email']);
        if ($user) {
            throw new HttpException(Codes::HTTP_BAD_REQUEST, "User with email = '{$params['email']}' already exits.");
        }

        $user = new User();
        $user->setEmail($params['email']);
        $user->setExternalId($params['externalId']);

        $user->setUserAgency($agency);

        if (!empty($params['userFirstName'])) {
            $user->setUserFirstName($params['userFirstName']);
        }
        if (!empty($params['userLastName'])) {
            $user->setUserLastName($params['userLastName']);
        }

        $user->setInternalUserName();
        // Check if user with such internal name exist
        if ($userRepository->findSimilarUserByInternalName($user->getInternalUserName()))
        {
            // If user with internal name exist, try to create from email
            $user->setInternalUserName(true);
        }

        $dm->persist($user);
        $dm->flush();

        $facetRepository = $this->getRepository('BpiApiBundle:Entity\UserFacet');
        $facetRepository->prepareFacet($user);

        $transform = $this->get('bpi.presentation.transformer');
        $transform->setDoc($this->get('bpi.presentation.users'));
        $document = $transform->transform($user);

        return $document;
    }

    /**
     * Edit user by id.
     *
     * @Rest\Post("/edit/{userId}")
     * @Rest\View()
     *
     * @param $userId
     *
     * @return \Bpi\RestMediaTypeBundle\Users
     */
    public function editUserAction($userId)
    {
        $userRepository = $this->getRepository('BpiApiBundle:Entity\User');
        $dm = $this->getDoctrineManager();
        $params = $this->getAllRequestParameters();
        $this->stripParams($params);

        $user = $userRepository->find($userId);

        if (null === $user) {
            throw new HttpException(Codes::HTTP_NOT_FOUND, 'User with id ' . $userId . ' not found.');
        }

        if (isset($params['userAgency']) && !empty($params['userAgency'])) {
            $agencyRepository = $this->getRepository('BpiApiBundle:Aggregate\Agency');
            $agency = $agencyRepository->findOneBy(
                array(
                    'public_id' => $params['userAgency'],
                    'deleted' => false,
                )
            );

            if (null === $agency) {
                throw new HttpException(Codes::HTTP_NOT_FOUND, 'Agency with id ' . $params['userAgency'] . ' not found.');
            }

            $user->setUserAgency($agency);
        }

        if (isset($params['externalId']) && !empty($params['externalId'])) {
            $user->setExternalId($params['externalId']);
        }

        if (isset($params['email']) && !empty($params['email'])) {
            $user->setEmail($params['email']);
        }

        if (isset($params['userFirstName']) && !empty($params['userFirstName'])) {
            $user->setUserFirstName($params['userFirstName']);
        }

        if (isset($params['userLastName']) && !empty($params['userLastName'])) {
            $user->setUserLastName($params['userLastName']);
        }

        $dm->persist($user);
        $dm->flush();

        $transform = $this->get('bpi.presentation.transformer');
        $transform->setDoc($this->get('bpi.presentation.users'));
        $document = $transform->transform($user);

        return $document;
    }

    /**
     * Create new user
     *
     * @Rest\Post("/autocompletions")
     * @Rest\View()
     */
    public function autocompletionsUserAction()
    {
        $userRepository = $this->getRepository('BpiApiBundle:Entity\User');
        $params = $this->getAllRequestParameters();
        // Strip all params.
        $this->stripParams($params);

        $agencyId = null;
        if (isset($params['agencyId']) && !empty($params['agencyId'])) {
            $agency = $this->getRepository('BpiApiBundle:Aggregate\Agency')->findOneBy(array('public_id' => $params['agencyId']));
            $agencyId = $agency ? $agency->getId() : 1;
        }

        $userIternalname = null;
        if (isset($params['userIternalName']) && !empty($params['userIternalName'])) {
            $userIternalname = $params['userIternalName'];
        }

        $users = $userRepository->getListAutocompletions($userIternalname, $agencyId);
        $transform = $this->get('bpi.presentation.transformer');
        $transform->setDoc($this->get('bpi.presentation.users'));
        $document = $transform->transformMany($users);
        return $document;
    }

    /**
     * Create subscription.
     *
     * @Rest\Post("/subscription")
     * @Rest\View()
     */
    public function createSubscriptionAction()
    {
        $dm = $this->getDoctrineManager();
        $userRepository = $this->getRepository('BpiApiBundle:Entity\User');
        $parameters = $this->getAllRequestParameters();
        $this->stripParams($parameters);

        // Check required parameters.
        $requiredParams = array(
            'title' => 0,
            'filter' => 0,
            'userId' => 0,
        );
        $this->checkParams($parameters, $requiredParams);
        foreach ($requiredParams as $param => $count) {
            if ($count == 0) {
                throw new HttpException(Codes::HTTP_BAD_REQUEST, "Param '{$param}' is required.");
            }
        }

        $user = $userRepository->find($parameters['userId']);
        if (null === $user) {
            throw new HttpException(Codes::HTTP_NOT_FOUND, "User with id = '{$parameters['userId']}' not fount.");
        }

        foreach ($user->getSubscriptions() as $subscription) {
            if ($subscription->getTitle() === $parameters['title']) {
                throw new HttpException(Codes::HTTP_BAD_REQUEST, "User already have subscription with this name.");
            }
        }

        // Create new subscription.
        $subscription = new Subscription();
        $subscription->setTitle($parameters['title']);
        $subscription->setFilter($parameters['filter']);
        $subscription->setLastViewed(new \DateTime());

        $user->addSubscription($subscription);
        $dm->persist($user);
        $dm->flush();

        $transform = $this->get('bpi.presentation.transformer');
        $transform->setDoc($this->get('bpi.presentation.users'));
        $document = $transform->transform($user);

        return $document;
    }

    /**
     * Remove subscription for particular user.
     *
     * @Rest\Post("/subscription/remove")
     * @Rest\View()
     */
    public function removeUserSubscriptionAction()
    {
        $dm = $this->getDoctrineManager();
        $userRepository = $this->getRepository('BpiApiBundle:Entity\User');
        $parameters = $this->getAllRequestParameters();

        // Strip all params.
        $this->stripParams($parameters);
        $requiredParams = array(
            'userId' => 0,
            'subscriptionTitle' => 0,
        );
        $this->checkParams($parameters, $requiredParams);
        foreach ($requiredParams as $param => $count) {
            if ($count == 0) {
                throw new HttpException(Codes::HTTP_BAD_REQUEST, "Param '{$param}' is required.");
            }
        }

        $user = $userRepository->find($parameters['userId']);
        if (null === $user) {
            throw new HttpException(Codes::HTTP_NOT_FOUND, "User with id = '{$parameters['userId']}' not found.");
        }

        foreach ($user->getSubscriptions() as $subscription) {
            if ($subscription->getTitle() === $parameters['subscriptionTitle']) {
                $user->removeSubscription($subscription);
            }
        }
        $dm->persist($user);
        $dm->flush();

        $transform = $this->get('bpi.presentation.transformer');
        $transform->setDoc($this->get('bpi.presentation.users'));
        $document = $transform->transform($user);

        return $document;
    }
}
