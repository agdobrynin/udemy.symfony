<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\UserConfirmationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

class UserConfirmationServiceTest extends TestCase
{
    private const CONFIRMATION_TOKEN= 'abcdefg';

    public function testConfirmUser()
    {
        $user = new User();
        $user->setConfirmationToken(self::CONFIRMATION_TOKEN);
        $this->assertEquals(false, $user->getIsActive());
        $this->assertEquals(self::CONFIRMATION_TOKEN, $user->getConfirmationToken());

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

        (new UserConfirmationService($userRepo, $em))->confirmUser(self::CONFIRMATION_TOKEN);
        $this->assertEquals(true, $user->getIsActive());
        $this->assertEquals(null, $user->getConfirmationToken());
    }
}
