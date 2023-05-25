<?php

namespace App\EventSubscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class JWTAuthenticationFailureListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'lexik_jwt_authentication.on_authentication_failure' => 'onAuthenticationFailure',
        ];
    }

    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $exception = $event->getException();

        $response = new JsonResponse([
            'code' => 'error',
            'message' => 'Authentification non reussie',
            'error' => $exception->getMessage()
        ], Response::HTTP_OK); 
        
        // Forced because Linkih app axios-http client don't correctly handle HTTP_UNAUTHAURIZED response
        // Clear cache just after this file edition
        // php bin/console cache:clear
        
        $event->setResponse($response);
    }
}
