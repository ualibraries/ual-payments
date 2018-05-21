<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Psr\Log\LoggerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        $this->logger->alert('Access to \'result\' route denied to request IP ' . $request->getClientIp());
        return "@Twig/Exception/error.txt.twig";
    }
}