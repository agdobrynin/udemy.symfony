<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Exception\EmptyBodyException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
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
        $request = $event->getRequest();
        $contentType = $request->getContentType();
        $route = $request->get('_route');
        $method = $request->getMethod();
        if ($route !== 'api' || !in_array($method, [Request::METHOD_PUT, Request::METHOD_POST]) || in_array($contentType, ['html', 'form'])) {
            return;
        }

        $data = $event->getRequest()->get('data');

        if (null === $data) {
            $exception = new EmptyBodyException('Body for POST/PUT request is empty');
            $event->setThrowable($exception);
        }
    }
}
