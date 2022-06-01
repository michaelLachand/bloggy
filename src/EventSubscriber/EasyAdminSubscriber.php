<?php

namespace App\EventSubscriber;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{

    public function __construct( private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function onBeforeEntityPersistedEvent(BeforeEntityPersistedEvent $event): void
    {
        $this->hashPasswordIfUserAndPlainPasswordProvided($event->getEntityInstance());
    }

    public function onBeforeEntityUpdatedEvent(BeforeEntityUpdatedEvent $event): void
    {
        $this->hashPasswordIfUserAndPlainPasswordProvided($event->getEntityInstance());
    }

    private function hashPasswordIfUserAndPlainPasswordProvided($entityInstance): void
    {
        if ( $entityInstance instanceof User && $entityInstance->plainPassword) {
            $hashedPassword = $this->userPasswordHasher->hashPassword($entityInstance, $entityInstance->plainPassword);

            $entityInstance->setPassword($hashedPassword);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => 'onBeforeEntityPersistedEvent',
            BeforeEntityUpdatedEvent::class => 'onBeforeEntityUpdatedEvent',
        ];
    }
}
