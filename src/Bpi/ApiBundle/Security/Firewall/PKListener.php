<?php

namespace Bpi\ApiBundle\Security\Firewall;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\RestBundle\EventListener\ViewResponseListener;
use FOS\RestBundle\View\View as FosView;

use Bpi\ApiBundle\Security\Authentication\Token\PKUserToken;

class PKListener implements ListenerInterface
{
    protected $tokenStorage;
    protected $authenticationManager;
    protected $container;

    /**
     * PKListener constructor.
     *
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage $tokenStorage
     *   Token storage.
     * @param \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface $authenticationManager
     *   Authentication manager.
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     *   Service container.
     */
    public function __construct(
        TokenStorage $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        ContainerInterface $container
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        try {
            if ($this->container->get('kernel')->getEnvironment() == 'test_skip_auth') {
                return $this->skipAuthorization();
            }

            if ($request->isMethod('OPTIONS')) {
                return $this->skipAuthorization();
            }

            $token = new PKUserToken();

            if ($request->headers->has('Auth')) {
                if (!preg_match('~BPI agency="(?<agency>[^"]+)", token="(?<token>[^"]+)"~i', $request->headers->get('Auth'), $matches)) {
                    throw new AuthenticationException('Authorization credentials required (HTTP Headers)');
                }

                $token->setUser($matches['agency']);
                $token->token = $matches['token'];
            } elseif ($request->query->has('_authorization')) {
                $auth = $request->query->get('_authorization');
                if (empty($auth['agency']) or empty($auth['token'])) {
                    throw new AuthenticationException('Authorization credentials required (GET)');
                }

                $token->setUser($auth['agency']);
                $token->token = $auth['token'];
            } else {
                throw new AuthenticationException('Authorization required (none)');
            }

            $authToken = $this->authenticationManager->authenticate($token);
            $this->tokenStorage->setToken($authToken);
        } catch (AuthenticationException $failed) {
            $view = new FosView($failed->getMessage(), 401);
            $controller_result_event = new GetResponseForControllerResultEvent(
                $event->getKernel(),
                $request,
                $event->getRequestType(),
                $view
            );

            /** @var \FOS\RestBundle\View\ViewHandler $viewHandler */
            $viewHandler = $this->container->get('fos_rest.view_handler');
            $view_listener = new ViewResponseListener(
                $viewHandler,
                true
            );
            $view_listener->onKernelView($controller_result_event);

            $event->setResponse($controller_result_event->getResponse());
        }
    }

    /**
     * Skips authorization check.
     */
    protected function skipAuthorization()
    {
        $this->tokenStorage->setToken(new PKUserToken());
    }
}
