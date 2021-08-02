<?php

declare(strict_types=1);

namespace App\DataFixtures;

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
        ['email' => 'nyasia01@hotmail.com', 'login' => 'nyasia', 'password' => 'PopLop245'],
        ['email' => 'utillman@rohan.org', 'login' => 'utillman', 'password' => '22KokLut1'],
        ['email' => 'kebert@connelly.info', 'login' => 'kebert', 'password' => '55UniNorm1'],
        ['email' => 'vanessa90@gmail.com', 'login' => 'vanessa', 'password' => 'YariLo123'],
        ['email' => 'hauck.celia@friesen.com', 'login' => 'hauck.celia', 'password' => 'JoinUsLite568'],
    ];

    /**
     * @var string[]
     */
    private $referenceUserKeys = [];
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
            $refKey = array_rand($this->referenceUserKeys);
            $blogPost->setAuthor($this->getReference($this->referenceUserKeys[$refKey]));
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
                $userRefKey = $this->referenceUserKeys[array_rand($this->referenceUserKeys)];

                $comment = (new Comment())
                    ->setPost($this->getReference($refKeyBlogPost))
                    ->setAuthor($this->getReference($userRefKey))
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
            $login=''; $email=''; $password='';
            extract($userSource);

            $user = (new User())
                ->setLogin($login)
                ->setEmail($email)
                ->setName($this->faker->name);

            $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));
            $refKey = 'user.id.' . $index;
            $this->addReference($refKey, $user);
            $this->referenceUserKeys[] = $refKey;
            $manager->persist($user);
        }

        $manager->flush();
    }
}
