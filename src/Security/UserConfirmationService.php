<?php

declare(strict_types=1);

namespace App\Security;

use App\Exception\InvalidConfirmationTokenException;
use App\Repository\UserRepository;
use App\Security\Interfaces\UserConfirmationServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class UserConfirmationService implements UserConfirmationServiceInterface
{
    private $userRepository;
    private $entityManager;
    private $logger;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function confirmUser(string $confirmToken): void
    {
        if ($user = $this->userRepository->findOneByConfirmToken($confirmToken)) {
            $this->logger->debug(sprintf('User found "%s" with login "%s" and email "%s"', $user->getName(), $user->getLogin(), $user->getEmail()));
            $user->setIsActive(true);
            $user->setConfirmationToken(null);
            $this->entityManager->flush();
            return;
        }

        $this->logger->debug(sprintf('User not found by token "%s"', $confirmToken));
        throw new InvalidConfirmationTokenException('Confirmation token is invalid.');
    }
}
