<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\UserConfirmation;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final class UserConfirmationSubscriber implements EventSubscriberInterface
{
    private $userRepository;
    private $entityManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['confirmUser', EventPriorities::POST_VALIDATE]
        ];
    }

    public function confirmUser(ViewEvent $event): void
    {
        /** @var UserConfirmation $userConfirmation */
        $userConfirmation = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$userConfirmation instanceof UserConfirmation && Request::METHOD_POST !== $method) {
            return;
        }

        if ($user = $this->userRepository->findOneByConfirmToken($userConfirmation->confirmationToken)) {
            $user->setIsActive(true);
            $user->setConfirmationToken(null);
            $this->entityManager->flush();
            $event->setResponse(new JsonResponse(null, Response::HTTP_NO_CONTENT));
        }

        throw new NotFoundHttpException('User not found.');
    }
}
