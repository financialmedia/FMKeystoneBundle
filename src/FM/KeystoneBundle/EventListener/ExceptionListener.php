<?php

namespace FM\KeystoneBundle\EventListener;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;

class ExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof AuthenticationException) {
            $event->setResponse(new Response($exception->getMessage(), 403));
        }

        if ($exception instanceof AccessDeniedHttpException) {
            $event->setResponse(new Response($exception->getMessage(), 401));
        }
    }
}
