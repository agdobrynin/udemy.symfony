<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Email\Mailer;
use App\Entity\User;
use App\Security\TokenGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserRegisterSubscriber implements EventSubscriberInterface
{
    private $userPasswordHasher;
    private $tokenGenerator;
    private $mailer;

    public function __construct(UserPasswordHasherInterface $hasher, TokenGenerator $tokenGenerator, Mailer $mailer)
    {
        $this->userPasswordHasher = $hasher;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailer = $mailer;
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

        $tokenConfirm = $this->tokenGenerator->getRandomSecureToken();
        $user->setConfirmationToken($tokenConfirm);
        $this->mailer->sendConfirmationLogin($user);
    }
}
