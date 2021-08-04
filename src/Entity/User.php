<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ApiResource(
 *     itemOperations={
 *          "get"={
 *              "normalization_context"={"groups"={"user:read"}}
 *          },
 *          "put"={
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object === user",
 *              "denormalization_context"={"groups"={"user:update"}},
 *              "normalization_context"={"groups"={"user:update"}}
 *          },
 *          "user_change_password"={
 *              "method"="put",
 *              "openapi_context"={
 *                  "summary"="Change curent password for auth user",
 *              },
 *              "path"="/users/change-password/{id}",
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object === user",
 *              "denormalization_context"={"groups"={"user:change-password"}},
 *              "normalization_context"={"groups"={"user:change-password"}}
 *         },
 *     },
 *     collectionOperations={
 *          "post"={
 *              "denormalization_context"={"groups"={"create"}}
 *          },
 *     },
 * )
 * @UniqueEntity(
 *     "login",
 *     message="Логин уже используется в системе. Придумайте другой логин"
 * )
 * @UniqueEntity(
 *     "email",
 *     message="Такой email уже используется в системе. Укажите другой email"
 * )
 * @method string getUserIdentifier()
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_MODERATOR = 'ROLE_MODERATOR';

    public const DEFAULT_ROLES = [self::ROLE_USER];
    public const ALL_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_USER,
        self::ROLE_MODERATOR,
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"user:create", "user:update"})
     * @Assert\NotBlank(message="Укажите логин пользователя")
     * @Assert\Length(
     *     min=5, minMessage="Логин должен быть более {{ limit }} символов",
     *     max=20, maxMessage="Максимальная длинна логина {{ limit }} символов",
     * )
     */
    private $login;
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"user:create", "user:change-password"})
     * @Assert\NotBlank(message="Укажите пароль пользователя")
     * @Assert\Length(
     *     min=6, minMessage="Минимальная длинна пароля {{ limit }} символов",
     *     max=255, maxMessage="Слишком большая длинна пароля. Максимальное количество символов {{ limit }}"
     * )
     * @Assert\Regex(
     *     pattern="/(?=.*[a-zа-яё])(?=.*[A-ZА-ЯЁ])(?=.*(.*\d){2})/",
     *     message="Пароль должен содержать одну заглавную букву, две цифры"
     * )
     */
    private $password;

    /**
     * @Groups({"user:create", "user:change-password"})
     * @Assert\NotBlank(message="Повторите пароль")
     * @Assert\Expression(
     *     "this.getPassword() === this.getPasswordRepeated()",
     *     message="Пароли не совпадают"
     * )
     */
    private $passwordRepeated;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"user:create", "admin:user:read", "owner:user:read"})
     * @Assert\NotBlank(message="Укажите email пользователя")
     * @Assert\Email(message="Указанный email некорректный")
     */
    private $email;

    /**
     * @ORM\Column(type="simple_array", length="255")
     * @Groups({"admin:user:read", "owner:user:read"})
     */
    private $roles;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"user:read", "user:create", "user:update", "get-comments-with-author", "get:read_post:with_author"})
     * @Assert\NotBlank(message="Укажите полное имя пользователя")
     * @Assert\Length(min=5, minMessage="Минимальная далинна имени пользователя {{ limit }} символов")
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BlogPost", mappedBy="author")
     * @Groups({"user:read"})
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="author")
     * @Groups({"user:read"})
     */
    private $comments;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->roles = self::DEFAULT_ROLES;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function getUsername(): ?string
    {
        return $this->getLogin();
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @Groups({"get"})
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function __call($name, $arguments)
    {
        return $this->getLogin();
    }

    public function getPasswordRepeated(): ?string
    {
        return $this->passwordRepeated;
    }

    public function setPasswordRepeated(?string $passwordRepeated = null): void
    {
        $this->passwordRepeated = $passwordRepeated;
    }
}
