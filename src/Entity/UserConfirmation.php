<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     itemOperations={
 *         "user_confirm_from_email"={
 *              "method"="get",
 *              "path": "/users/confirm/{confirmationToken}",
 *          },
 *     },
 *     collectionOperations={
 *         "post"={
 *             "path"="/users/confirm",
 *          },
 *     }
 * )
 */
final class UserConfirmation
{
    /**
     * @ApiProperty(identifier=true)
     * @Assert\NotBlank
     * @Assert\Length(min=40, max=40)
     */
    public $confirmationToken;
}
