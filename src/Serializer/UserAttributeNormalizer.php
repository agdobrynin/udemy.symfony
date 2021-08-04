<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Encoder\NormalizationAwareInterface;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

final class UserAttributeNormalizer implements ContextAwareNormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    private const ALREADY_CALLED = 'USER_ATTRIBUTE_NORMALIZER_ALREADY_CALLED';
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof User;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        if ($this->userHasPermissions($object)) {
            $context['groups'][] = 'owner:user:read';
        }

        if (!$this->serializer instanceof NormalizerInterface) {
            $message = sprintf('Cannot normalize object "%s" because inject serializer is not normalizer.', $object);
            throw new LogicException($message);
        }

        $context[self::ALREADY_CALLED] = true;

        return $this->serializer->normalize($object, $format, $context);
    }

    /**
     * @param User $object
     */
    private function userHasPermissions($object): bool
    {
        if ($token = $this->tokenStorage->getToken()) {
            return $object->getLogin() === $token->getUser()->getUserIdentifier();
        }

        return false;
    }
}
