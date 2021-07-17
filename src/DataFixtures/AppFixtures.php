<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const REFERENCE_TEST_USER_1 = 'test.user.1';
    private const REFERENCE_TEST_USER_2 = 'test.user.2';

    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadBlogPosts($manager);
    }

    public function loadBlogPosts(ObjectManager $manager)
    {
        $blogPost = new BlogPost();
        $blogPost->setTitle('Yess');
        $blogPost->setAuthor($this->getReference(self::REFERENCE_TEST_USER_1));
        $blogPost->setContent('Eligendi non quis exercitationem culpa nesciunt nihil aut nostrum explicabo.');
        $blogPost->setCreatedAt(new \DateTime());
        $blogPost->setSlug('lo-lo');
        $manager->persist($blogPost);

        $blogPost = new BlogPost();
        $blogPost->setTitle('Post forman');
        $blogPost->setAuthor($this->getReference(self::REFERENCE_TEST_USER_2));
        $blogPost->setContent('Lorem ipsum dolor sit amet, consectetur adipisicing elit.');
        $blogPost->setCreatedAt(new \DateTime());
        $blogPost->setSlug('ko-ko');
        $manager->persist($blogPost);

        $manager->flush();
    }

    public function loadComments(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadBlogPosts($manager);
    }

    public function loadUsers(ObjectManager $manager)
    {
        $user1 = (new User())
            ->setLogin('alex.d')
            ->setName('Alex D.')
            ->setEmail('alex.d@gmail.com');
        $user1->setPassword($this->userPasswordHasher->hashPassword($user1, 'qwerty#2'));

        $this->addReference(self::REFERENCE_TEST_USER_1, $user1);
        $manager->persist($user1);

        $user2 = (new User())
            ->setLogin('klim.v')
            ->setName('Klim Voroshilov')
            ->setEmail('klim@mail.ru');
        $user2->setPassword($this->userPasswordHasher->hashPassword($user2, '#2qwerty'));

        $this->addReference(self::REFERENCE_TEST_USER_2, $user2);
        $manager->persist($user2);

        $manager->flush();
    }
}
