<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\AuthorEntityInterface;
use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const MAX_BLOG_POST = 50;
    private const MAX_COMMENTS = 25;
    private const USERS = [
        ['email' => 'nyasia01@hotmail.com', 'login' => 'nyasia', 'password' => 'PopLop245', 'roles' => [User::ROLE_ADMIN]],
        ['email' => 'utillman@rohan.org', 'login' => 'utillman', 'password' => '22KokLut1', 'roles' => [User::ROLE_USER, User::ROLE_MODERATOR]],
        ['email' => 'kebert@connelly.info', 'login' => 'kebert', 'password' => '55UniNorm1', 'roles' => [User::ROLE_USER]],
        ['email' => 'vanessa90@gmail.com', 'login' => 'vanessa', 'password' => 'YariLo123', 'roles' => [User::ROLE_MODERATOR]],
        ['email' => 'hauck.celia@friesen.com', 'login' => 'hauck.celia', 'password' => 'JoinUsLite568', 'roles' => [User::ROLE_USER]],
    ];

    /**
     * @var string[]
     */
    private $referenceUserKeys = [];
    private $referenceUserKeysForComment;
    private $referenceUserKeysForPost;
    private $referenceBlogPostKeys = [];
    private $userPasswordHasher;
    private $faker;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->faker = Factory::create();
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
        foreach (self::USERS as $index => $userSource) {
            $login=''; $email=''; $password=''; $roles=[];
            extract($userSource);

            $user = (new User())
                ->setLogin($login)
                ->setEmail($email)
                ->setName($this->faker->name)
                ->setRoles($roles);

            $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));
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
