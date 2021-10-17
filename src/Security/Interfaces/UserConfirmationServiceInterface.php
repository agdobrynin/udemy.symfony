<?php

declare(strict_types=1);

namespace App\Security\Interfaces;

interface UserConfirmationServiceInterface
{
    public function confirmUser(string $confirmToken): void;
}
