<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use App\Security\TokenGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserRegisterSubscriber implements EventSubscriberInterface
{
    private $userPasswordHasher;
    private $tokenGenerator;

    public function __construct(UserPasswordHasherInterface $hasher, TokenGenerator $tokenGenerator)
    {
        $this->userPasswordHasher = $hasher;
        $this->tokenGenerator = $tokenGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['userRegistered', EventPriorities::PRE_WRITE]
        ];
    }

    public function userRegistered(ViewEvent $event): void
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$user instanceof User || Request::METHOD_POST !== $method) {
            return;
        }

        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));

        $user->setConfirmationToken($this->tokenGenerator->getRandomSecureToken());
    }
}
