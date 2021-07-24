<?php

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
    private const MAX_USERS = 5;
    private const MAX_COMMENTS = 25;

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
        for ($i = 0; $i < self::MAX_USERS; $i += 1) {
            $user = (new User())
                ->setLogin($this->faker->userName)
                ->setName($this->faker->name)
                ->setEmail($this->faker->email);

            $user->setPassword($this->userPasswordHasher->hashPassword($user, 'qwerty#2'));
            $refKey = 'user.id.' . $i;
            $this->addReference($refKey, $user);
            $this->referenceUserKeys[] = $refKey;
            $manager->persist($user);
        }

        $manager->flush();
    }
}
