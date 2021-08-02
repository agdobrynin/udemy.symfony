<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

interface AuthorEntityInterface
{
    public function setAuthor(UserInterface $user): AuthorEntityInterface;
}
