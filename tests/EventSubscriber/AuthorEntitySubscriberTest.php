<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\EventSubscriber\AuthorEntitySubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;

class AuthorEntitySubscriberTest extends TestCase
{
    public function testConfigSubscriber()
    {
        $res = AuthorEntitySubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::VIEW, $res);
        $this->assertEquals(['getAuthUser', EventPriorities::PRE_VALIDATE], $res[KernelEvents::VIEW]);
    }
}
