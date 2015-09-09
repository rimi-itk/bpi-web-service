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

use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\Entity\History;
use Bpi\ApiBundle\Domain\Entity\User;
use Bpi\ApiBundle\Domain\XmlResponse\XmlError;

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
        $userRepository = $this->getRepository('BpiApiBundle:Entity\User');

        $allUsers = $userRepository->findAll();

        $transform = $this->get('bpi.presentation.transformer');
        $transform->setDoc($this->get('bpi.presentation.users'));
        $document = $transform->transformMany($allUsers);

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
        $xmlError = $this->get('bpi.presentation.xmlerror');
        $statusCode = 201;
        $dm = $this->getDoctrineManager();
        $userRepository = $this->getRepository('BpiApiBundle:Entity\User');
        $agencyRepository = $this->getRepository('BpiApiBundle:Aggregate\Agency');
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
                $xmlError->setCode(400);
                $xmlError->setError("Param '{$param}' is required.");
                return $xmlError;
            }
        }

        if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
            $xmlError->setCode(400);
            $xmlError->setError("Email '{$params['email']}' not valid.");
            return $xmlError;
        }

        // Get agency.
        $agency = $this->getAgencyFromHeader();
        $user = $userRepository->findByExternalIdAgency($params['externalId'], $agency->getId());
        if ($user) {
            $xmlError->setCode(400);
            $xmlError->setError( "User with externalId = '{$params['externalId']}' and agency = '{$agency->getId()}' already exits.");
            return $xmlError;
        }

        $user = $userRepository->findOneByEmail($params['email']);
        if ($user) {
            $xmlError->setCode(400);
            $xmlError->setError( "User with email = '{$params['email']}' already exits.");
            return $xmlError;
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
        $xmlError = $this->get('bpi.presentation.xmlerror');
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
                $xmlError->setCode(400);
                $xmlError->setError("Param '{$param}' is required.");
                return $xmlError;
            }
        }

        $user = $userRepository->find($parameters['userId']);
        if (null === $user) {
            $xmlError->setCode(404);
            $xmlError->setError("User with id = '{$parameters['userId']}' not fount.");
            return $xmlError;
        }

        foreach ($user->getSubscriptions() as $subscription) {
            if ($subscription->getTitle() === $parameters['title']) {
                $xmlError->setCode(400);
                $xmlError->setError("User already have subscription with this name.");
                return $xmlError;
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
}
