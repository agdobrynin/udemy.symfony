<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use App\Entity\UserConfirmation;
use App\EventSubscriber\UserConfirmationSubscriber;
use App\Security\TokenGenerator;
use App\Security\UserConfirmationService;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class UserConfirmationSubscriberTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testConfigSubscriber()
    {
        $res = UserConfirmationSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::VIEW, $res);
        $this->assertEquals(['confirmUser', EventPriorities::POST_VALIDATE], $res[KernelEvents::VIEW]);
    }

    public function dataProvider(): array
    {
        $entity = new UserConfirmation();
        $entity->confirmationToken = (new TokenGenerator())->getRandomSecureToken();

        $entityOther = new User();

        return [
            [Request::METHOD_POST, $this->once(), $entity],
            [Request::METHOD_GET, $this->never(), $entity],
            [Request::METHOD_POST, $this->never(), $entityOther],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testConfirmUser(string $method, InvokedCount $count, $entity)
    {
        $confirmServiceMock = $this->getMockBuilder(UserConfirmationService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event = $this->getEventMock($method, $entity);
        $confirmServiceMock->expects($count)->method('confirmUser');
        (new UserConfirmationSubscriber($confirmServiceMock))->confirmUser($event);
    }

    /**
     * @return MockObject|ViewEvent
     */
    private function getEventMock(string $method, $controllerResult)
    {
        $requestMock = $this->getMockBuilder(Request::class)->getMock();
        $requestMock->expects($this->once())->method('getMethod')->willReturn($method);

        $eventMock = $this->getMockBuilder(ViewEvent::class)
            ->disableOriginalConstructor()->getMock();

        $eventMock->expects($this->once())->method('getControllerResult')->willReturn($controllerResult);
        $eventMock->expects($this->once())->method('getRequest')->willReturn($requestMock);

        return $eventMock;
    }
}
