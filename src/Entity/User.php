<?php

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
 *     itemOperations={"get"},
 *     collectionOperations={"post"},
 *     normalizationContext={
 *          "groups" = {"read"}
 *     }
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
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank(message="Укажите логин пользователя")
     * @Assert\Length(
     *     min=5, minMessage="Логин должен быть более {{ limit }} символов",
     *     max=20, maxMessage="Максимальная длинна логина {{ limit }} символов",
     * )
     */
    private $login;
    /**
     * @ORM\Column(type="string", length=255)
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
     * @Assert\NotBlank(message="Повторите пароль")
     * @Assert\Expression(
     *     "this.getPassword() === this.getPasswordRepeated()",
     *     message="Пароли не совпадают"
     * )
     */
    private $passwordRepeated;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read"})
     * @Assert\NotBlank(message="Укажите email пользователя")
     * @Assert\Email(message="Указанный email некорректный")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read"})
     * @Assert\NotBlank(message="Укажите полное имя пользователя")
     * @Assert\Length(min=5, minMessage="Минимальная далинна имени пользователя {{ limit }} символов")
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BlogPost", mappedBy="author")
     * @Groups({"read"})
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="author")
     * @Groups({"read"})
     */
    private $comments;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
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
     * @Groups({"read"})
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUsername()
    {
        // TODO: Implement getUsername() method.
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement @method string getUserIdentifier()
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
