<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     itemOperations={
 *          "user_confirm_from_email"={
 *              "openapi_context"={
 *                  "summary"="Activate user account by token",
 *                  "request"={},
 *                  "responses"={
 *                      "302"={
 *                          "headers"={
 *                              "Location"={
 *                                  "description"="redirect uri to default route, main page",
 *                                  "schema"={
 *                                      "type"="string",
 *                                      "format"="uri",
 *                                  },
 *                              },
 *                          },
 *                          "description"="Success active and redirect to default route",
 *                      },
 *                      "200"={
 *                          "content",
 *                          "description"="Success active",
 *                      },
 *                  },
 *              },
 *          }
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
