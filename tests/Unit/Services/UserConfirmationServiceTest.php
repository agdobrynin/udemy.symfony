<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\TokenGenerator;
use App\Security\UserConfirmationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

class UserConfirmationServiceTest extends TestCase
{
    private $confirmationToken;

    public function setUp(): void
    {
        parent::setUp();
        $this->confirmationToken = (new TokenGenerator())->getRandomSecureToken();
    }

    public function testConfirmUser()
    {
        $user = new User();
        $user->setConfirmationToken($this->confirmationToken);
        $this->assertEquals(false, $user->getIsActive());
        $this->assertEquals($this->confirmationToken, $user->getConfirmationToken());

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->expects($this->once())
            ->method('findOneByConfirmToken')
            ->willReturn($user);

        $objectManager = $this->createMock(ObjectManager::class);

        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($userRepo);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        (new UserConfirmationService($userRepo, $em))->confirmUser($this->confirmationToken);
        $this->assertEquals(true, $user->getIsActive());
        $this->assertEquals(null, $user->getConfirmationToken());
    }
}
