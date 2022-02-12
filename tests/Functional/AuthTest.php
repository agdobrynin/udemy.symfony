<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\FixtureUser;

class AuthTest extends ApiTestCase
{
    private const ENDPOINT_AUTH = '/api/login_check';

    public function authProvider(): array
    {
        return [
            [FixtureUser::ADMIN_LOGIN, FixtureUser::ADMIN_PASSWORD, 200],
            ['x', 'x', 401],
        ];
    }

    /**
     * @dataProvider authProvider
     */
    public function testAuthCheck(string $login, string $password, int $statusCode): void
    {
        $response = static::createClient()
            ->request('POST', self::ENDPOINT_AUTH, ['json' => ['username' => $login, 'password' => $password]]);

        if (200 === $response->getStatusCode()) {
            $this->assertArrayHasKey('token', json_decode($response->getContent(false), true));

        }

        $this->assertResponseStatusCodeSame($statusCode);
    }
}
