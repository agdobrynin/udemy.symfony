<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\FixtureUser;
use App\Entity\Comment;
use App\Tests\Functional\Helpers\Auth;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class CommentTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private const ENDPOINT_ADD_COMMENT = '/api/comments';

    public function testGetComments(): void
    {
        $client = Auth::createAuthClient(FixtureUser::ADMIN_LOGIN);
        $client->request('GET', self::ENDPOINT_ADD_COMMENT);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertMatchesResourceCollectionJsonSchema(Comment::class);
    }
}
