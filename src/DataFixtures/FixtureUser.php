<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;

class FixtureUser
{
    public const ADMIN_LOGIN = 'nyasia';
    public const ADMIN_PASSWORD = 'SaSa145';
    public $email;
    public $login;
    public $password = 'SaSa145';
    public $roles = [User::ROLE_USER];
    public $isActive;
    public $confirmationToken = null;

    public function __construct(string $email, string $login, bool $isActive = true)
    {
        $this->email = $email;
        $this->login = $login;
        $this->isActive = $isActive;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
}
