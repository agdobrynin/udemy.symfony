<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ChangePasswordAction
{
    private $validator;
    private $userPasswordHasher;
    private $em;
    private $tokenManager;

    public function __construct(
        ValidatorInterface          $validator,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $em,
        JWTTokenManagerInterface    $tokenManager
    )
    {
        $this->validator = $validator;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->em = $em;
        $this->tokenManager = $tokenManager;
    }

    public function __invoke(User $user): JsonResponse
    {
        $this->validator->validate($user);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPasswordNew()));
        // Поле смены пароля старый токен еще валиден, его надо скинуть.
        // Для Entity действие persist выполнится автоматически.
        $this->em->flush();
        $newToken = $this->tokenManager->create($user);

        return new JsonResponse(['token' => $newToken]);
    }
}
