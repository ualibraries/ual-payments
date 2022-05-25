<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    private $router;
    private $shibUaid;

    public function __construct(RouterInterface $router, $shibUaid)
    {
        $this->router = $router;
        $this->shibUaid = $shibUaid;
    }

    /**
     * Creates a Response object to send upon a successful logout.
     *
     * @param Request $request
     * @return Response never null
     */
    public function onLogoutSuccess(Request $request)
    {
        if ($request->server->has($this->shibUaid)) {
            return new RedirectResponse($this->router->generate('shib_logout',
                array(
                    'return' => $this->router->generate('login')
                )));
        } else {
            return new RedirectResponse($this->router->generate('login'));
        }
    }
}
