<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\MediaObject;
use App\Entity\User;
use App\EventSubscriber\AuthorEntitySubscriber;
use DG\BypassFinals;
use PHPUnit\Framework\MockObject\MockObject;
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
            [BlogPost::class, Request::METHOD_POST, true],
            [Comment::class, Request::METHOD_POST, true],
            [MediaObject::class, Request::METHOD_POST, false],
            [BlogPost::class, Request::METHOD_GET, false],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testSetAuthorCall(string $entityClassName, string $httpMethod, bool $shouldBeSetAuthor)
    {
        $entityMock = $this->getEntityMock($entityClassName, $shouldBeSetAuthor);
        $eventMock = $this->getEventMock($httpMethod, $entityMock);
        $token = $this->getTokenStorageMock();
        (new AuthorEntitySubscriber($token))->getAuthUser($eventMock);
    }

    public function testNoToken()
    {
        $token = $this->getTokenStorageMock(false);
        $eventMock = $this->getEventMock(Request::METHOD_POST, new class {});
        (new AuthorEntitySubscriber($token))->getAuthUser($eventMock);
    }

    /**
     * @return MockObject|TokenStorageInterface
     */
    private function getTokenStorageMock(bool $hasToken = true)
    {
        $tokenMock = $this->getMockBuilder(TokenInterface::class)->getMockForAbstractClass();
        $tokenMock->expects($hasToken ? $this->once() : $this->never())
            ->method('getUser')->willReturn(new User());

        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)->getMockForAbstractClass();
        $tokenStorageMock->method('getToken')->willReturn($hasToken ? $tokenMock : null);

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

    private function getEntityMock(string $className, bool $shouldBeSetAuthor): MockObject
    {
        $entityMock = $this->getMockBuilder($className)->setMethods(['setAuthor'])->getMock();
        $entityMock->expects($shouldBeSetAuthor ? $this->once() : $this->never())->method('setAuthor');

        return $entityMock;
    }
}
