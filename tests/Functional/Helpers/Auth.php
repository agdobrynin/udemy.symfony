<?php

declare(strict_types=1);

namespace App\Tests\Functional\Helpers;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\User;

class Auth extends ApiTestCase

{
    public static function createAuthClient(string $login): Client
    {
        $user = self::getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['login' => $login]);
        $token = self::getContainer()->get('lexik_jwt_authentication.jwt_manager')->create($user);
        $client = static::createClient();
        $client->setDefaultOptions(['auth_bearer' => $token]);

        return $client;
    }
}
