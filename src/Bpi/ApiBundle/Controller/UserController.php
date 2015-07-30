<?php
/**
 * @file
 *  User controller class
 */
namespace Bpi\ApiBundle\Controller;

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
        $dm = $this->getDoctrineManager();
        $serializer = $this->getSerialilzer();
        $userRepository = $this->getRepository('BpiApiBundle:Entity\User');
        $userData = $this->getAllRequestParameters();

        $user = new User();
        foreach ($userData as $data) {
        }
    }
}
