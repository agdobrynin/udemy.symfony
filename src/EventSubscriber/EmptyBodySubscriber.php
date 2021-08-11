<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Exception\EmptyBodyException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class EmptyBodySubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['handleEmptyBody', EventPriorities::PRE_DESERIALIZE],
        ];
    }

    public function handleEmptyBody(ExceptionEvent $event): void
    {
        $method = $event->getRequest()->getMethod();
        if (!in_array($method, [Request::METHOD_PUT, Request::METHOD_POST])) {
            return;
        }

        $data = $event->getRequest()->get('data');

        if (null === $data) {
            throw new EmptyBodyException(Response::HTTP_BAD_REQUEST, 'Body for POST/PUT request is empty');
        }
    }
}
