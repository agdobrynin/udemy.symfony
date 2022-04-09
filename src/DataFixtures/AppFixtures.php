<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\MediaObject;
use App\Entity\User;
use App\Security\TokenGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const MAX_BLOG_POST = 50;
    private const MAX_COMMENTS = 25;

    private $referenceMediaObjectKeys = [];
    /**
     * @var FixtureUser[]
     */
    private $users = [];
    private $referenceUserKeys = [];
    private $referenceUserKeysForComment;
    private $referenceUserKeysForPost;
    private $referenceBlogPostKeys = [];
    private $userPasswordHasher;
    private $faker;
    private $generator;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher, TokenGenerator $generator, ContainerBagInterface $container)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->faker = Factory::create();
        // Admin user
        $admin = (new FixtureUser('nyasia01@hotmail.com', $container->get('app.fixture.admin.login')))
            ->setRoles([User::ROLE_ADMIN]);
        $admin->setPassword($container->get('app.fixture.admin.password'));

        $this->users[] = $admin;
        // Other users
        for ($i = 0; $i < 5; $i++) {
            $this->users[] = $this->userRandom($container->get('app.fixture.user.password'));
        }

        $this->generator = $generator;
    }

    public function userRandom(string $password): FixtureUser
    {
        $role = User::ALL_ROLES[array_rand(User::ALL_ROLES)];

        return (new FixtureUser($this->faker->email, $this->faker->userName))
            ->setRoles([$role])
            ->setPassword($password);
    }

    public function load(ObjectManager $manager)
    {
        $this->loadMediaObjects($manager);
        $this->loadUsers($manager);
        $this->loadBlogPosts($manager);
        $this->loadComments($manager);
    }

    public function loadMediaObjects(ObjectManager $manager)
    {
        $fixtureDirImages = realpath(__DIR__.DIRECTORY_SEPARATOR.'images');
        $dirImages = dir($fixtureDirImages);

        while (false !== ($entry = $dirImages->read())) {
            if (!in_array($entry, ['.', '..'], true)) {
                $file = $fixtureDirImages.DIRECTORY_SEPARATOR.$entry;
                $mimeType = mime_content_type($file) ?: '';
                $tmpFile = tempnam(sys_get_temp_dir(), uniqid());
                copy($file, $tmpFile);
                $fileUploaded = new UploadedFile($tmpFile, $entry, $mimeType, null, true);
                $mediaObject = new MediaObject();
                $mediaObject->setFile($fileUploaded);
                $mediaObject->mimeType = $mimeType;
                $manager->persist($mediaObject);
                $refKey = "media_object_{$file}";
                $this->addReference($refKey, $mediaObject);
                $this->referenceMediaObjectKeys[] = $refKey;
            }
        }

        $manager->flush();
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

            foreach ($this->getRandomMediaObjects(2) as $mediaObject) {
                $blogPost->addMediaObject($mediaObject);
            }

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

    /**
     * @return MediaObject[]
     */
    private function getRandomMediaObjects(int $max = 2): array
    {
        $total =count($this->referenceMediaObjectKeys);

        if ($total < $max) {
            $message = sprintf('Нельзя задать количество элементов "%d" из MediaObject так как их всего "%d"', $max, $total);
            throw new \UnexpectedValueException($message);
        }

        $rand = [];

        foreach (array_rand($this->referenceMediaObjectKeys, $max) as $randIndex) {
            $rand[] = $this->getReference($this->referenceMediaObjectKeys[$randIndex]);
        }

        return $rand;
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
