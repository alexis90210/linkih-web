<?php

namespace App\EventSubscriber;

use App\Entity\Utilisateurs;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class JWTAuthenticationSuccessListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'lexik_jwt_authentication.on_authentication_success' => 'onAuthenticationSuccess',
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        
        // Retrieve the user's ID from the authenticated token
        $user = $event->getUser();
        $userId = $user instanceof Utilisateurs ? $user->getId() : null;
        $userCompteActif = $user instanceof Utilisateurs ? $user->getCompteActif() : null;
        $userCompteConfirme = $user instanceof Utilisateurs ? $user->getCompteConfirme() : null;
        
        // Add the "id" field to the response
        $data['id'] = $userId;
        $data['compte_actif'] = $userCompteActif;
        $data['compte_confirme'] = $userCompteConfirme;
        
        $event->setData($data);
    }
}
