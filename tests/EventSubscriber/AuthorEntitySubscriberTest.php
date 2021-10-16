<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\BlogPost;
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
    private $token;

    public function setUp(): void
    {
        parent::setUp();
        BypassFinals::enable();
        $this->token = $this->getTokenStorageMock();
    }

    public function testConfigSubscriber()
    {
        $res = AuthorEntitySubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::VIEW, $res);
        $this->assertEquals(['getAuthUser', EventPriorities::PRE_VALIDATE], $res[KernelEvents::VIEW]);
    }

    public function testSetAuthorCall()
    {
        $entityMock = $this->getEntityMock(BlogPost::class, true);
        $eventMock = $this->getEventMock(Request::METHOD_POST, $entityMock);
        (new AuthorEntitySubscriber($this->token))->getAuthUser($eventMock);
    }

    public function testSetAuthorNoCall()
    {
        $entityMock = $this->getEntityMock('NonExistClass', false);
        $eventMock = $this->getEventMock(Request::METHOD_GET, $entityMock);
        (new AuthorEntitySubscriber($this->token))->getAuthUser($eventMock);

    }

    /**
     * @return MockObject|TokenStorageInterface
     */
    private function getTokenStorageMock()
    {
        $tokenMock = $this->getMockBuilder(TokenInterface::class)->getMockForAbstractClass();
        $tokenMock->method('getUser')->willReturn(new User());

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

    private function getEntityMock(string $className, bool $shouldBeSetAuthor): MockObject
    {
        $entityMock = $this->getMockBuilder($className)->setMethods(['setAuthor'])->getMock();
        $entityMock->expects($shouldBeSetAuthor ? $this->once() : $this->never())
            ->method('setAuthor');

        return $entityMock;
    }
}
