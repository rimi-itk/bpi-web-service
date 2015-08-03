<?php
/**
 * @file
 *  User controller class
 */
namespace Bpi\ApiBundle\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\Entity\History;
use Bpi\ApiBundle\Domain\Entity\User;

/**
 * Class UserController
 * @package Bpi\ApiBundle\Controller
 *
 * Rest controller for Users
 */
class UserController extends BPIController
{
    private $userData = array();

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
        $serializer = $this->getSerialilzer();

        $allUsers = $userRepository->findAll();

        return new Response('All users', 200);
    }

    /**
     * Create new user
     *
     * @Rest\Post("/")
     * @Rest\View()
     */
    public function createUserAction()
    {
        $statusCode = 201;
        $dm = $this->getDoctrineManager();
        $serializer = $this->getSerialilzer();
        $userRepository = $this->getRepository('BpiApiBundle:Entity\User');
        $agencyRepository = $this->getRepository('BpiApiBundle:Aggregate\Agency');
        $userData = $this->getAllRequestParameters();
        $requiredUserData = array(
            'externalId',
            'email',
            'agencyPublicId'
        );

        // Check if all required user data was sent in request
        foreach ($requiredUserData as $reqData) {
            if (!isset($userData[$reqData]) || empty($userData[$reqData])) {
                $errorMessage = sprintf('%s required for user creation.', $reqData);
                $statusCode = 400;
            }
        }

        //Get agency and check if exist
        $userAgency = $agencyRepository->findOneBy(array('public_id' => $userData['agencyPublicId']));
        if (null === $userAgency) {
            $errorMessage = sprintf('Agency with agencyPublicId = %s dosn\'t exist.', $userData['agencyPublicId']);
            $statusCode = 404;
        }

        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $errorMessage = sprintf('Email %s not valid.', $userData['email']);
            $statusCode = 400;
        }

        if (!empty($errorMessage)) {
            throw new HttpException($statusCode, $errorMessage);
        }

        $user = new User();
        $user->setEmail($userData['email']);
        $user->setExternalId($userData['externalId']);
        $user->setUserAgency($userAgency);
        if (!empty($userData['userFirstName'])) {
            $user->setUserFirstName($userData['userFirstName']);
        }
        if (!empty($userData['userLastName'])) {
            $user->setUserLastName($userData['userLastName']);
        }
        $user->setInternalUserName();

        // Check if user with such internal name exist
        if ($userRepository->findSimilarByInternalName($user->getInternalUserName()))
        {
            // If user with internal name exist, try to create from email
            $user->setInternalUserName(true);
        }

        $dm->persist($user);
        $dm->flush();

        // TODO: Output xml using RestMediaTypeBundle
        $responseMessage = sprintf('User with internal BPI name %s created', $user->getInternalUserName());
        return new Response($responseMessage, $statusCode);
    }
}
