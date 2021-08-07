<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UserConfirmationService
{
    private $userRepository;
    private $entityManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }
    public function confirmUser(string $confirmToken)
    {
        if ($user = $this->userRepository->findOneByConfirmToken($confirmToken)) {
            $user->setIsActive(true);
            $user->setConfirmationToken(null);
            $this->entityManager->flush();
            return;
        }

        throw new NotFoundHttpException('User not found.');
    }
}
