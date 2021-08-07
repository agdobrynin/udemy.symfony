<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\AuthorEntityInterface;
use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use App\Security\TokenGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const MAX_BLOG_POST = 50;
    private const MAX_COMMENTS = 25;
    /**
     * @var FixtureUser[]
     */
    private $users = [];
    /**
     * @var string[]
     */
    private $referenceUserKeys = [];
    private $referenceUserKeysForComment;
    private $referenceUserKeysForPost;
    private $referenceBlogPostKeys = [];
    private $userPasswordHasher;
    private $faker;
    private $generator;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher, TokenGenerator $generator)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->faker = Factory::create();
        $this->users[] = (new FixtureUser('nyasia01@hotmail.com', 'nyasia'))->setRoles([User::ROLE_ADMIN]);
        $this->users[] = (new FixtureUser('utillman@rohan.org', 'utillman'))->setRoles([User::ROLE_USER, User::ROLE_MODERATOR]);
        $this->users[] = (new FixtureUser('kebert@connelly.info', 'kebert', false))->setRoles([User::ROLE_USER]);
        $this->users[] = (new FixtureUser('vanessa90@gmail.com', 'vanessa90'))->setRoles([User::ROLE_MODERATOR]);
        $this->users[] = (new FixtureUser('hauck.celia@friesen.com', 'hauck.celia'))->setRoles([User::ROLE_USER]);
        $this->generator = $generator;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadBlogPosts($manager);
        $this->loadComments($manager);
    }

    public function loadBlogPosts(ObjectManager $manager)
    {
        for ($i = 0; $i < self::MAX_BLOG_POST; $i += 1) {
            $blogPost = new BlogPost();
            $blogPost->setTitle($this->faker->text(30));
            $blogPost->setAuthor($this->getRandomUser($blogPost));
            $blogPost->setContent($this->faker->realText());
            $blogPost->setCreatedAt($this->faker->dateTimeThisYear());
            $blogPost->setSlug($this->faker->slug);
            $refKey = "blogpost.{$i}";
            $this->addReference($refKey, $blogPost);
            $this->referenceBlogPostKeys[] = $refKey;
            $manager->persist($blogPost);
        }

        $manager->flush();
    }

    public function loadComments(ObjectManager $manager)
    {
        foreach ($this->referenceBlogPostKeys as $refKeyBlogPost) {
            for ($i = 0; $i < rand(1, self::MAX_COMMENTS); $i += 1) {

                $comment = new Comment();
                $comment->setPost($this->getReference($refKeyBlogPost))
                    ->setAuthor($this->getRandomUser($comment))
                    ->setCreatedAt($this->faker->dateTimeThisMonth())
                    ->setContent($this->faker->text)
                    ->setIsPublished(true);
                $manager->persist($comment);
            }
        }

        $manager->flush();
    }

    public function loadUsers(ObjectManager $manager)
    {
        foreach ($this->users as $index => $userSrc) {

            $user = (new User())
                ->setLogin($userSrc->login)
                ->setEmail($userSrc->email)
                ->setName($this->faker->name)
                ->setIsActive($userSrc->isActive)
                ->setRoles($userSrc->roles);

            if (!$userSrc->isActive) {
                $user->setConfirmationToken($this->generator->getRandomSecureToken());
            }

            $user->setPassword($this->userPasswordHasher->hashPassword($user, $userSrc->password));
            $refKey = 'user.id.' . $index;
            $this->addReference($refKey, $user);
            $this->referenceUserKeys[] = $refKey;
            $manager->persist($user);
        }

        $manager->flush();
    }

    private function getRandomUser($entity): User
    {
        if ($entity instanceof Comment) {
            if (!$this->referenceUserKeysForComment) {
                $this->referenceUserKeysForComment = array_filter($this->referenceUserKeys, function (string $key) {
                    // Все пользователи авторизованные могут добавить комментарий
                    return (bool)array_intersect(User::ALL_ROLES,  $this->getReference($key)->getRoles());
                });
            }

            return $this->getReference($this->referenceUserKeys[array_rand($this->referenceUserKeysForComment)]);
        }

        if ($entity instanceof BlogPost) {
            if (!$this->referenceUserKeysForPost) {
                $this->referenceUserKeysForPost = array_filter($this->referenceUserKeys, function (string $key) {
                    // Размещать пост в блоге могут только роли "пользователь", "админ"
                    return (bool)array_intersect([User::ROLE_USER, User::ROLE_ADMIN], $this->getReference($key)->getRoles());
                });
            }

            return $this->getReference($this->referenceUserKeys[array_rand($this->referenceUserKeysForPost)]);
        }

        $errorMessage = sprintf('Ups! Input Entity not support %s', get_class($entity));

        throw new \UnexpectedValueException($errorMessage);
    }
}
