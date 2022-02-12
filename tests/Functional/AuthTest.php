<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\FixtureUser;
use Symfony\Component\HttpFoundation\Response;

class AuthTest extends ApiTestCase
{
    private const ENDPOINT_AUTH = '/api/login_check';

    public function testLoginSuccess(): void
    {
        static::createClient()->request('POST', self::ENDPOINT_AUTH, ['json' => [
            'username' => FixtureUser::ADMIN_LOGIN,
            'password' => FixtureUser::ADMIN_PASSWORD,
        ]]);

        $this->assertResponseIsSuccessful();
    }

    public function testLoginFailed(): void
    {
        static::createClient()->request('POST', self::ENDPOINT_AUTH, ['json' => [
            'username' => 'admin',
            'password' => 'admin',
        ]]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
