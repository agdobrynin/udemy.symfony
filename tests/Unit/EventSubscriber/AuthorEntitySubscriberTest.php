<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\MediaObject;
use App\Entity\User;
use App\EventSubscriber\AuthorEntitySubscriber;
use DG\BypassFinals;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthorEntitySubscriberTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        BypassFinals::enable();
    }

    public function testConfigSubscriber()
    {
        $res = AuthorEntitySubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::VIEW, $res);
        $this->assertEquals(['getAuthUser', EventPriorities::PRE_VALIDATE], $res[KernelEvents::VIEW]);
    }

    public function dataProvider(): array
    {
        return [
            [BlogPost::class, Request::METHOD_POST, $this->once()],
            [Comment::class, Request::METHOD_POST, $this->once()],
            [MediaObject::class, Request::METHOD_POST, $this->never()],
            [BlogPost::class, Request::METHOD_GET, $this->never()],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testSetAuthorCall(string $entityClassName, string $httpMethod, InvokedCount $shouldBeSetAuthor)
    {
        $entityMock = $this->getEntityMock($entityClassName, $shouldBeSetAuthor);
        $eventMock = $this->getEventMock($httpMethod, $entityMock);
        $token = $this->getTokenStorageMock($this->once());
        (new AuthorEntitySubscriber($token))->getAuthUser($eventMock);
    }

    public function testNoToken()
    {
        $token = $this->getTokenStorageMock($this->never());
        $eventMock = $this->getEventMock(Request::METHOD_POST, new class {});
        (new AuthorEntitySubscriber($token))->getAuthUser($eventMock);
    }

    /**
     * @return MockObject|TokenStorageInterface
     */
    private function getTokenStorageMock(InvokedCount $invokedCount)
    {
        $tokenMock = null;

        if (!$invokedCount->isNever()) {
            $tokenMock = $this->getMockBuilder(TokenInterface::class)->getMockForAbstractClass();
            $tokenMock->expects($invokedCount)->method('getUser')->willReturn(new User());
        }

        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)->getMockForAbstractClass();
        $tokenStorageMock->method('getToken')->willReturn($tokenMock);

        return $tokenStorageMock;
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

    private function getEntityMock(string $className, InvokedCount $invocationOrder): MockObject
    {
        $entityMock = $this->getMockBuilder($className)->setMethods(['setAuthor'])->getMock();
        $entityMock->expects($invocationOrder)->method('setAuthor');

        return $entityMock;
    }
}
