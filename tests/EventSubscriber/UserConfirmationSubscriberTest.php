<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\EventSubscriber\UserConfirmationSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;

class UserConfirmationSubscriberTest extends TestCase
{
    public function testConfigSubscriber()
    {
        $res = UserConfirmationSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::VIEW, $res);
        $this->assertEquals(['confirmUser', EventPriorities::POST_VALIDATE], $res[KernelEvents::VIEW]);
    }
}
