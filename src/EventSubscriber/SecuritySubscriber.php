<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Psr\Log\LoggerInterface;

class SecuritySubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onAuthenticationFailure',
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
        );
    }

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $exception = $event->getAuthenticationException();
        $token = $event->getAuthenticationToken();
        $creds = $token->getCredentials();
        $this->logger->error("Login failed for " . $creds['username'] . ": " . $exception->getMessage());
    }

    public function onAuthenticationSuccess(AuthenticationEvent $event)
    {
        $token = $event->getAuthenticationToken();
        if ($token->getUsername() != "anon") {
            $this->logger->info("Login succeeded: " . $token->getUsername());
        }
    }
}
