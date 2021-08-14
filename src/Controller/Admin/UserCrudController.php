<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {

        $this->userPasswordHasher = $userPasswordHasher;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entity): void
    {
        $this->encodeUserPassword($entity);
        parent::persistEntity($entityManager, $entity);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entity): void
    {
        $this->encodeUserPassword($entity);
        parent::updateEntity($entityManager, $entity);
    }

    /**
     * @param User $entity
     */
    private function encodeUserPassword(User $user): void
    {
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));
    }
}
