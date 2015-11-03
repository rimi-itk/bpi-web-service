<?php
/**
 * @file
 *  Notification controller class.
 */

namespace Bpi\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints;

use Bpi\ApiBundle\Domain\Entity\History;
use Bpi\ApiBundle\Domain\Entity\User;


/**
 * Class NotificationsController
 * @package Bpi\AdminBundle\Controller
 */
class NotificationsController extends Controller
{
    /**
     * @return \Bpi\ApiBundle\Domain\Repository\AgencyRepository
     */
    private function getRepository($repositoryName)
    {
        return $this->get('doctrine.odm.mongodb.document_manager')
            ->getRepository($repositoryName);
    }

    /**
     * @Template("BpiApiBundle:Notifications:runNotifications.html.twig")
     */
    public function runNotificationsAction()
    {
        $userRepository = $this->getRepository('BpiApiBundle:Entity\User');
        $allUsers = $userRepository->findAll();
        $userNotifications = array();
        foreach ($allUsers as $user) {
            $userId = $user->getId();
            $userNotification = $userRepository->getUserNotifications($user);
            if (!empty($userNotification)) {
                $notification[$userId] = $userNotification;
            }
            if (!isset($notification[$userId])) {
                continue;
            }
            $notification[$userId]['user'] = $user;
            $userNotifications = $notification;
        }

        foreach ($userNotifications as $notif) {
            $subject = 'Hello ' . $notif['user']->getUserFirstName() . ' ' . $notif['user']->getUserFirstName() . ', you have new updates in BPI system.';
            $messageBody = $this->renderView('BpiAdminBundle:Notifications:runNotifications.html.twig', array(
                'notification' => $notif
            ));

            $message = new \Swift_Message();
            $message = $message::newInstance();
            $message->setSubject($subject);
            $message->setSender('makcumm@gmail.com');
            $message->setTo($notif['user']->getEmail());
            $message->setBody($messageBody);

            $this->get('mailer')->send($message);
        }

        return new Response('Messsages sent to users.');
    }
}
