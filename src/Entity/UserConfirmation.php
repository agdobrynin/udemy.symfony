<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     itemOperations={},
 *     collectionOperations={
 *         "post"={
 *             "path"="/users/confirm",
 *          }
 *     }
 * )
 */
final class UserConfirmation
{
    /**
     * @ApiProperty
     * @Assert\NotBlank
     * @Assert\Length(min=40, max=40)
     */
    public $confirmationToken;
}
