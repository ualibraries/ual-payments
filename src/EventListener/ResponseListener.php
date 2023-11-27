<?php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseListener implements EventSubscriberInterface
{

    /**
     * Add x-frame-options header to mitigate clickjacking
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        $event->getResponse()->headers->set('x-frame-options', 'deny');
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array('onKernelResponse', -1024),
        );
    }
}
