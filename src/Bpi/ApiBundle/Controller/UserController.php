<?php

namespace Bpi\ApiBundle\Controller;

use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Domain\Entity\UserFacet;
use Bpi\ApiBundle\Domain\ValueObject\Subscription;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations as Rest;

use Bpi\ApiBundle\Domain\Entity\User;
use Bpi\RestMediaTypeBundle\Element\Facet;
use Bpi\RestMediaTypeBundle\Element\FacetTerm;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class UserController.
 *
 * TODO: Unknown purpose.
 * @deprecated
 */
class UserController extends FOSRestController
{
    use BpiRequestParamSanitizerTrait;

    const USER_LIST_COUNT = 10;

    /**
     * List all users
     *
     * @Rest\Get("/user")
     * @Rest\View()
     * @return \Bpi\RestMediaTypeBundle\XmlResponse
     */
    public function listUsersAction(Request $request)
    {
        $query = new \Bpi\ApiBundle\Domain\Entity\UserQuery();
        if ($amount = $request->query->get('amount', self::USER_LIST_COUNT)) {
            $query->amount($amount);
        }

        if ($offset = $request->query->get('offset')) {
            $query->offset($offset);
        }

        if ($search = $request->query->get('search')) {
            $query->search($search);
        }

        $filters = array();
        $logicalOperator = '';
        if ($filter = $request->query->get('filter', [])) {
            foreach ($filter as $field => $value) {
                if ($field == 'agency_id' && is_array($value)) {
                    foreach ($value as $val) {
                        if (empty($val)) {
                            continue;
                        }
                        $filters['agency_id'][] = $val;
                    }
                }
            }
            if (isset($filter['logicalOperator']) && !empty($filter['logicalOperator'])) {
                $logicalOperator = $filter['logicalOperator'];
            }
        }

        /** @var \Bpi\ApiBundle\Domain\Repository\UserFacetRepository $userFacetRepository */
        $userFacetRepository = $this->get('doctrine_mongodb')->getRepository(UserFacet::class);
        $facets = $userFacetRepository->getFacetsByRequest($filters, $logicalOperator);
        $query->filter($facets->userIds);

        if ($sort = $request->query->get('sort', [])) {
            foreach ($sort as $field => $order) {
                $query->sort($field, $order);
            }
        }

        /** @var \Bpi\ApiBundle\Domain\Repository\UserRepository $userRepository */
        $userRepository = $this->get('doctrine_mongodb')->getRepository(User::class);
        $users = $userRepository->findByQuery($query);

        if (!$users) {
            throw new NotFoundHttpException('No users found.');
        }

        /** @var \Bpi\RestMediaTypeBundle\Users $response */
        $response = $this->get('bpi.presentation.users');
        $response->setTotal($query->total);
        $response->setOffset($query->offset);
        $response->setAmount($query->amount);

        foreach ($facets->facets as $name => $facet) {
            $theFacet = new Facet(Facet::TYPE_STRING, $name);
            foreach ($facet as $key => $term) {
                $value = $term;
                $title = null;
                if ($name == 'agency_id') {
                    $value = $term['count'];
                    $title = $term['agencyName'];
                } elseif (isset($term['count'])) {
                    $value = $term['count'];
                    $title = isset($term['title']) ? $term['title'] : null;
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
     * @param string $userId The user id in database.
     *
     * @Rest\Get("/user/{id}")
     * @Rest\View()
     *
     * @return \Bpi\RestMediaTypeBundle\XmlResponse
     */
    public function getUserByIdAction(User $user)
    {
        /** @var \Bpi\RestMediaTypeBundle\Users $response */
        $response = $this->get('bpi.presentation.users');
        $response->addUser($user);

        return $response;
    }

    /**
     * Create new user
     *
     * @Rest\Post("/user")
     * @Rest\View(statusCode="201")
     */
    public function createUserAction(Request $request)
    {
        /** @var \Doctrine\Common\Persistence\ObjectManager $dm */
        $dm = $this->get('doctrine_mongodb')->getManager();
        /** @var \Bpi\ApiBundle\Domain\Repository\UserRepository $userRepository */
        $userRepository = $dm->getRepository(User::class);
        $params = $request->request->all();
        // Strip all params.
        $this->stripParams($params);

        $requiredParams = array(
            'externalId' => 0,
            'email' => 0,
        );
        $this->checkParams($params, $requiredParams);

        foreach ($requiredParams as $param => $count) {
            if (!$count) {
                throw new BadRequestHttpException( "Param '{$param}' is required.");
            }
        }

        if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
            throw new BadRequestHttpException("Email '{$params['email']}' not valid.");
        }

        /** @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->get('security.token_storage');
        /** @var \Bpi\ApiBundle\Domain\Aggregate\Agency $agency */
        $agency = $tokenStorage->getToken()->getUser();

        /** @var User $user */
        $user = $userRepository->findByExternalIdAgency($params['externalId'], $agency->getId());
        if ($user) {
            throw new BadRequestHttpException( "User with externalId = '{$params['externalId']}' and agency = '{$agency->getId()}' already exits.");
        }

        $user = $userRepository->findOneBy(['email' => $params['email']]);
        if ($user) {
            throw new BadRequestHttpException( "User with email = '{$params['email']}' already exits.");
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

        /** @var \Bpi\ApiBundle\Domain\Repository\UserFacetRepository $userFacetRepository */
        $userFacetRepository = $dm->getRepository(UserFacet::class);
        $userFacetRepository->prepareFacet($user);

        $transform = $this->get('bpi.presentation.transformer');
        $transform->setDoc($this->get('bpi.presentation.users'));
        $document = $transform->transform($user);

        return $document;
    }

    /**
     * Edit user by id.
     *
     * @Rest\Get("/user/edit/{id}")
     * @Rest\View()
     *
     * @return \Bpi\RestMediaTypeBundle\Users
     */
    public function editUserAction(Request $request, User $user)
    {
        /** @var \Doctrine\Common\Persistence\ObjectManager $dm */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $params = $request->query->all();
        $this->stripParams($params);

        if (isset($params['userAgency']) && !empty($params['userAgency'])) {
            /** @var \Bpi\ApiBundle\Domain\Repository\AgencyRepository $agencyRepository */
            $agencyRepository = $dm->getRepository(Agency::class);
            /** @var Agency $agency */
            $agency = $agencyRepository->findOneBy(
                array(
                    'public_id' => $params['userAgency'],
                    'deleted' => false,
                )
            );

            if (!$agency) {
                throw new NotFoundHttpException( 'Agency with id ' . $params['userAgency'] . ' not found.');
            }

            $user->setUserAgency($agency);
        }

        if (!empty($params['externalId'])) {
            $user->setExternalId($params['externalId']);
        }

        if (!empty($params['email'])) {
            $user->setEmail($params['email']);
        }

        if (!empty($params['userFirstName'])) {
            $user->setUserFirstName($params['userFirstName']);
        }

        if (!empty($params['userLastName'])) {
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
     * Create subscription.
     *
     * @Rest\Post("/user/subscription")
     * @Rest\View()
     */
    public function createSubscriptionAction(Request $request)
    {
        /** @var \Doctrine\Common\Persistence\ObjectManager $dm */
        $dm = $this->get('doctrine_mongodb')->getManager();
        /** @var \Bpi\ApiBundle\Domain\Repository\UserRepository $userRepository */
        $userRepository = $dm->getRepository(User::class);
        $parameters = $request->request->all();
        $this->stripParams($parameters);

        // Check required parameters.
        $requiredParams = array(
            'title' => 0,
            'filter' => 0,
            'userId' => 0,
        );
        $this->checkParams($parameters, $requiredParams);
        foreach ($requiredParams as $param => $count) {
            if (!$count) {
                throw new BadRequestHttpException("Param '{$param}' is required.");
            }
        }

        /** @var \Bpi\ApiBundle\Domain\Entity\User $user */
        $user = $userRepository->find($parameters['userId']);
        if ($user) {
            throw new NotFoundHttpException("User with id = '{$parameters['userId']}' not fount.");
        }

        /** @var \Bpi\ApiBundle\Domain\ValueObject\Subscription $subscription */
        foreach ($user->getSubscriptions() as $subscription) {
            if ($subscription->getTitle() === $parameters['title']) {
                throw new BadRequestHttpException("User already have subscription with this name.");
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
     * @Rest\Post("/user/subscription/remove")
     * @Rest\View()
     */
    public function removeUserSubscriptionAction(Request $request)
    {
        /** @var \Doctrine\Common\Persistence\ObjectManager $dm */
        $dm = $this->get('doctrine_mongodb')->getManager();
        /** @var \Bpi\ApiBundle\Domain\Repository\UserRepository $userRepository */
        $userRepository = $dm->getRepository(User::class);
        $parameters = $request->request->all();

        // Strip all params.
        $this->stripParams($parameters);
        $requiredParams = array(
            'userId' => 0,
            'subscriptionTitle' => 0,
        );
        $this->checkParams($parameters, $requiredParams);
        foreach ($requiredParams as $param => $count) {
            if (!$count) {
                throw new BadRequestHttpException("Param '{$param}' is required.");
            }
        }

        /** @var \Bpi\ApiBundle\Domain\Entity\User $user */
        $user = $userRepository->find($parameters['userId']);
        if (!$user) {
            throw new NotFoundHttpException("User with id = '{$parameters['userId']}' not found.");
        }

        /** @var \Bpi\ApiBundle\Domain\ValueObject\Subscription $subscription */
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
