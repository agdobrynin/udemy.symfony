<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\FixtureUser;

class AuthTest extends ApiTestCase
{
    private const ENDPOINT_AUTH = '/api/login_check';

    public function testAuthSuccess(): void
    {
        $client = static::createClient();
        $client->request('POST', self::ENDPOINT_AUTH, static::getData(FixtureUser::ADMIN_LOGIN, FixtureUser::ADMIN_PASSWORD));
        $this->assertResponseIsSuccessful();
    }

    public function testAuthFail(): void
    {
        $client = static::createClient();
        $client->request('POST', self::ENDPOINT_AUTH, static::getData('user', '----lalala---'));
        $this->assertResponseStatusCodeSame(401);
    }

    private static function getData(string $login, string $password): array
    {
        return  [
            'json' => [
                'username' => $login,
                'password' => $password,
            ],
        ];
    }
}
