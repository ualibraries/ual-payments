<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\Security\Http\Logout\SessionLogoutHandler;

class LogoutHandler extends SessionLogoutHandler implements LogoutSuccessHandlerInterface
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Creates a Response object to send upon a successful logout.
     *
     * @param Request $request
     * @return Response never null
     */
    public function onLogoutSuccess(Request $request)
    {
        if ($request->server->has('Shib-uaId')) {
            $redirectTo = $this->router->generate('shib_logout', array(
                'return' => 'https://shibboleth.arizona.edu/cgi-bin/logout.pl'
            ));
            return new RedirectResponse($redirectTo);
        } else {
            return new RedirectResponse($this->router->generate('login'));
        }
    }
}
