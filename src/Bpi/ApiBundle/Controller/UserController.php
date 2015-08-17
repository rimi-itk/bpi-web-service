<?php
/**
 * @file
 *  User controller class
 */
namespace Bpi\ApiBundle\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\HttpFoundation\Response;

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

        // TODO: Output xml using RestMediaTypeBundle
        return new Response('All users', 200);
    }

    /**
     * Create new user
     *
     * @Rest\Post("/create")
     * @Rest\View()
     */
    public function createUserAction()
    {
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
                throw new HttpException(400, "Param '{$param}' is required.");
            }
        }

        if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
            throw new HttpException(400, "Email '{$params['email']}' not valid.");
        }

        // Get agency.
        $agency = $this->getAgencyFromHeader();
        $user = $userRepository->findByExternalIdAgency($params['externalId'], $agency->getId());
        if ($user) {
            $id = $agency->getAgencyId();
            throw new HttpException(400, "User with externalId = '{$params['externalId']}' and agency = '{$id}' already exits.");
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

        // TODO: Output xml using RestMediaTypeBundle
        $responseMessage = sprintf('User with internal BPI name %s created', $user->getInternalUserName());
        return new Response($responseMessage, $statusCode);
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
        $document = $this->get("bpi.presentation.transformer")->transformMany($users);
        return $document;
    }
}
