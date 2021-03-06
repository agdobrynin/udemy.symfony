<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"comment:read"}},
 *     attributes={
 *          "order"={"createdAt":"desc"},
 *     },
 *     itemOperations={
 *          "get",
 *          "put"={
 *              "access_control"="(is_granted('ROLE_ADMIN') or is_granted('ROLE_MODERATOR')) or (is_granted('ROLE_USER') and object.getAuthor() === user)",
 *              "denormalization_context"={"groups"={"comment:update"}},
 *          },
 *          "comment_approved"={
 *              "method"="put",
 *              "openapi_context"={
 *                  "summary"="Approved comment",
 *              },
 *              "path"="/comments/{id}/approved",
 *              "access_control"="is_granted('ROLE_MODERATOR') or is_granted('ROLE_ADMIN') ",
 *              "denormalization_context"={"groups"={"comment-denormalization:approved"}},
 *              "normalization_context"={"groups"={"comment-normalization:approved"}}
 *          }
 *     },
 *     collectionOperations={
 *          "get",
 *          "post"={
 *              "access_control"="is_granted('ROLE_USER') or is_granted('ROLE_MODERATOR')",
 *              "denormalization_context"={"groups"={"comment:create"}},
 *              "normalization_context"={
 *                 "groups"={"get-comments-with-author"}
 *             }
 *          },
 *     },
 *     subresourceOperations={
 *         "api_blog_posts_comments_get_subresource"={
 *             "normalization_context"={
 *                 "groups"={"get-comments-with-author"}
 *             }
 *         }
 *     },
 * )
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Comment implements AuthorEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"comment:read", "get-comments-with-author"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Groups({"comment:update", "comment:create", "comment:read", "get-comments-with-author", "comment-normalization:approved"})
     * @Assert\NotBlank
     * @Assert\Length(min=25)
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"comment:read", "get-comments-with-author", "comment-normalization:approved"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"comment:read", "get-comments-with-author", "comment-normalization:approved"})
     */
    private $updateAt;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"comment:read", "get-comments-with-author", "comment-normalization:approved", "comment-denormalization:approved"})
     */
    private $isPublished;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"comment:read", "get-comments-with-author"})
     * @Assert\NotBlank
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BlogPost", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"comment:create", "comment:read"})
     * @Assert\NotBlank
     */
    private $post;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(UserInterface $user): AuthorEntityInterface
    {
        $this->author = $user;

        return $this;
    }

    public function getPost(): ?BlogPost
    {
        return $this->post;
    }

    public function setPost(BlogPost $post): self
    {
        $this->post = $post;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(\DateTimeInterface $updateAt): self
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setDatesAutomatically(): void
    {
        $this->setUpdateAt(new \DateTime());

        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTime());
        }
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setIsPublishedAutomatically(): void
    {
        if (!$this->getId()) {
            $rolesAvailablePublishComments = [User::ROLE_ADMIN, User::ROLE_MODERATOR];
            if (array_intersect($rolesAvailablePublishComments, $this->getAuthor()->getRoles())) {
                $this->setIsPublished(true);

                return;
            }

            $this->setIsPublished(false);
        }
    }

    public function __toString(): ?string
    {
        return $this->id . ':' . $this->content . ' by ' . $this->getAuthor()->getName() ?? 'not assign yet';
    }
}
