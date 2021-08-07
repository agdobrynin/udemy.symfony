<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\ChangePasswordAction;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
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
 *              "normalization_context"={"groups"={"user:update"}},
 *              "validation_groups"={"user:update"},
 *          },
 *          "user_change_password"={
 *              "method"="put",
 *              "openapi_context"={
 *                  "summary"="Change curent password for auth user",
 *                  "responses"={
 *                      "200"={
 *                          "content"={
 *                              "application/json"={
 *                                  "schema" = {
 *                                      "type" = "object",
 *                                      "properties" = {
 *                                          "token" = {
 *                                              "type"="string",
 *                                              "example"="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MjgxNTA4NTksImV4cCI6MTYyODE1NDQ1OSwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoia2ViZXJ0In0.gNatp-vyVkulZn9hHB-C9TYpgXKanEmFZeVGEPRFdMpfSHU4VX0G_bQ-IEB6jPavNicCwn-ACv9WEP6ffq5y3e1NYw19_eEGTvcyIdlBlCIuvHjx2upXCRSdU7CAVKte4scXWwoIVVrOiDLm8qrH903LWR3Y-0qmPQ3N-37JQgHiZAPJrVglQNLOJOtMHPkdxo8gkTTSQLV14YddRLRmUrkP-8oS6-Kg7HQy0n_jiLoVrumFIfN2JIwhjsswN8bNo2OYgXTJgh1ditD5L58u6Us9Eyt_YpXSDtMH_1YhBx2onlL326cNb1vgtV3NeJ3nkFYcTdflT6NLSqtee0Rmog",
 *                                          },
 *                                      },
 *                                  },
 *                              },
 *                          },
 *                      }
 *                  },
 *              },
 *              "path"="/users/{id}/change-password",
 *              "controller"=ChangePasswordAction::class,
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object === user",
 *              "validation_groups"={"user:change-password"},
 *              "denormalization_context"={
 *                  "groups"={"user:change-password"},
 *              },
 *         },
 *     },
 *     collectionOperations={
 *          "post"={
 *              "validation_groups"={"user:create"},
 *              "denormalization_context"={"groups"={"user:create"}},
 *              "normalization_context"={"groups"={"user:create-ok"}}
 *          },
 *     },
 * )
 * @UniqueEntity(
 *     groups={"user:create"},
 *     "login",
 *     message="Логин уже используется в системе. Придумайте другой логин"
 * )
 * @UniqueEntity(
 *      groups={"user:create"},
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
     * @Assert\NotBlank(
     *     groups={"user:create"},
     *     message="Укажите логин пользователя")
     * @Assert\Length(
     *     groups={"user:create"},
     *     min=5, minMessage="Логин должен быть более {{ limit }} символов",
     *     max=20, maxMessage="Максимальная длинна логина {{ limit }} символов")
     */
    private $login;
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"user:create"})
     * @Assert\NotBlank(
     *     groups={"user:create"},
     *     message="Укажите пароль пользователя")
     * @Assert\Length(
     *     groups={"user:create"},
     *     min=6,
     *     minMessage="Минимальная длинна пароля {{ limit }} символов",
     *     max=255, maxMessage="Слишком большая длинна пароля. Максимальное количество символов {{ limit }}"
     * )
     * @Assert\Regex(
     *     groups={"user:create"},
     *     pattern="/(?=.*[a-zа-яё])(?=.*[A-ZА-ЯЁ])(?=.*(.*\d){2})/",
     *     message="Пароль должен содержать одну заглавную букву, две цифры"
     * )
     */
    private $password;

    /**
     * @Groups({"user:create"})
     * @Assert\NotBlank(
     *     groups={"user:create"},
     *     message="Повторите пароль")
     * @Assert\Expression(
     *     groups={"user:create"},
     *     "this.getPassword() === this.getPasswordRepeated()",
     *     message="Пароли не совпадают")
     */
    private $passwordRepeated;

    /**
     * @Groups({"user:change-password"})
     * @Assert\NotBlank(
     *     groups={"user:change-password"},
     *     message="Укажите пароль пользователя")
     * @Assert\Length(
     *     groups={"user:change-password"},
     *     min=6,
     *     minMessage="Минимальная длинна пароля {{ limit }} символов",
     *     max=255, maxMessage="Слишком большая длинна пароля. Максимальное количество символов {{ limit }}")
     * @Assert\Regex(
     *     groups={"user:change-password"},
     *     pattern="/(?=.*[a-zа-яё])(?=.*[A-ZА-ЯЁ])(?=.*(.*\d){2})/",
     *     message="Пароль должен содержать одну заглавную букву, две цифры"
     * )
     */
    private $passwordNew;

    /**
     * @Groups({"user:change-password"})
     * @Assert\NotBlank(
     *     groups={"user:change-password"},
     *     message="Повторите пароль")
     * @Assert\Expression(
     *     groups={"user:change-password"},
     *     "this.getPasswordNew() === this.getPasswordNewRepeated()",
     *     message="Новые пароли не совпадают. Проверьте пароли и повторите ввод."
     * )
     */
    private $passwordNewRepeated;

    /**
     * @Groups({"user:change-password"})
     * @Assert\NotBlank(groups={"user:change-password"})
     * @UserPassword(
     *     groups={"user:change-password"},
     *     message="Существующий пароль неверный")
     */
    private $passwordOld;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"user:create", "admin:user:read", "owner:user:read"})
     * @Assert\NotBlank(
     *     groups={"user:create"},
     *     message="Укажите email пользователя")
     * @Assert\Email(
     *     groups={"user:create"},
     *     message="Указанный email некорректный")
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
     * @Assert\NotBlank(
     *     message="Укажите полное имя пользователя")
     * @Assert\Length(
     *     min=5,
     *     minMessage="Минимальная далинна имени пользователя {{ limit }} символов")
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

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $passwordChangedTimeStamp;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", nullable=true, length=40)
     */
    private $confirmationToken;

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

    public function getPasswordNew(): ?string
    {
        return $this->passwordNew;
    }

    public function setPasswordNew(?string $passwordNew = null): void
    {
        $this->passwordNew = $passwordNew;
    }

    public function getPasswordNewRepeated(): ?string
    {
        return $this->passwordNewRepeated;
    }

    public function setPasswordNewRepeated(?string $passwordNewRepeated = null): void
    {
        $this->passwordNewRepeated = $passwordNewRepeated;
    }

    public function getPasswordOld(): ?string
    {
        return $this->passwordOld;
    }

    public function setPasswordOld(?string $passwordOld = null): void
    {
        $this->passwordOld = $passwordOld;
    }

    public function getPasswordChangedTimeStamp(): ?int
    {
        return $this->passwordChangedTimeStamp;
    }

    public function setPasswordChangedTimeStamp(?int $passwordChangedTimeStamp = null): self
    {
        $this->passwordChangedTimeStamp = $passwordChangedTimeStamp;

        return $this;
    }

    public function getIsActive(): bool
    {
        return (bool)$this->isActive;
    }

    public function setIsActive(?bool $isActive = null): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken = null): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }
}
