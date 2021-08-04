<?php

declare(strict_types=1);

namespace App\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class UserContextBuilder implements SerializerContextBuilderInterface
{
    private $builder;
    private $checker;

    public function __construct(SerializerContextBuilderInterface $builder, AuthorizationCheckerInterface $checker)
    {
        $this->builder = $builder;
        $this->checker = $checker;
    }

    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        $context = $this->builder->createFromRequest($request, $normalization, $extractedAttributes);

        $resourceClass = $context['resource_class'] ?? null;

        if ($resourceClass === User::class && isset($context['groups']) && $this->checker->isGranted(User::ROLE_ADMIN)) {
            $context['groups'][] = 'admin:user:read';
        }

        return $context;
    }
}
