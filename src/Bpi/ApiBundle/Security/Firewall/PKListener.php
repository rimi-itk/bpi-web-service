<?php
namespace Bpi\ApiBundle\Security\Firewall;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\RestBundle\EventListener\ViewResponseListener;

use Bpi\ApiBundle\Security\Authentication\Token\PKUserToken;

class PKListener implements ListenerInterface
{
    protected $securityContext;
    protected $authenticationManager;
    protected $container;

    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, ContainerInterface $container)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->container = $container;
    }

    /**
     * Handle authorization by HTTP Authorization header     *
     * <code>Authorization: BPI pk="public_key", token="token"</code>
     *
     * Or by query string
     * <code>http://example.com/page?_authorization[pk]=public_key&_authorization[token]=token</code>
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @throws AuthenticationException
     */
    public function handle(GetResponseEvent $event)
    {
        try {

            if ($this->container->get('kernel')->getEnvironment() == 'test_skip_auth')
                return $this->skipAuthorization();

            $request = $event->getRequest();
            $token = new PKUserToken();
            if ($request->isMethod('OPTIONS'))
                return $this->skipAuthorization();

            if ($request->headers->has('Auth')) {
                if (!preg_match('~BPI agency="(?<agency>[^"]+)", pk="(?<pk>[^"]+)", token="(?<token>[^"]+)"~i', $request->headers->get('Auth'), $matches)) {
                    throw new AuthenticationException('Authorization credintials required (HTTP Headers)');
                }

                $token->setUser($matches['pk']);
                $token->token = $matches['token'];

            } elseif ($request->query->has('_authorization')) {

                $auth = $request->query->get('_authorization');
                if (empty($auth['pk']) or empty($auth['token'])) {
                    throw new AuthenticationException('Authorization credintials required (GET)');
                }

                $token->setUser($auth['pk']);
                $token->token = $auth['token'];

            } else {
                throw new AuthenticationException('Authorization required (none)');
            }

            $authToken = $this->authenticationManager->authenticate($token);
            $this->securityContext->setToken($authToken);

        } catch (AuthenticationException $failed) {

            //simulate kernel.view event to be able REST response listener do their job
            $view = new \FOS\RestBundle\View\View($failed->getMessage(), 401);
            $controller_result_event = new GetResponseForControllerResultEvent($event->getKernel(), $request, $event->getRequestType(), $view);

            $view_listener = new ViewResponseListener($this->container);
            $view_listener->onKernelView($controller_result_event);

            $event->setResponse($controller_result_event->getResponse());
        }
    }

    /**
     * Skip authorization check
     * User remains unauthorized
     */
    protected function skipAuthorization()
    {
        $this->securityContext->setToken(new PKUserToken());
    }
}
