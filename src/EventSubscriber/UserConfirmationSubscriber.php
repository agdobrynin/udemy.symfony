<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\UserConfirmation;
use App\Security\UserConfirmationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class UserConfirmationSubscriber implements EventSubscriberInterface
{
    private $userConfirmationService;

    public function __construct(UserConfirmationService $userConfirmationService)
    {
        $this->userConfirmationService = $userConfirmationService;
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

        if (!$userConfirmation instanceof UserConfirmation || Request::METHOD_POST !== $method) {
            return;
        }

        $this->userConfirmationService->confirmUser($userConfirmation->confirmationToken);
        $event->setResponse(new JsonResponse(null, Response::HTTP_NO_CONTENT));
    }
}
