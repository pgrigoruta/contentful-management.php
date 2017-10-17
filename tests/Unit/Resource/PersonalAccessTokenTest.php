<?php

/**
 * This file is part of the contentful-management.php package.
 *
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */
declare(strict_types=1);

namespace Contentful\Tests\Management\Unit\Resource;

use Contentful\Management\Resource\PersonalAccessToken;
use Contentful\Tests\Management\BaseTestCase;

class PersonalAccessTokenTest extends BaseTestCase
{
    public function testGetSetData()
    {
        $personalAccessToken = new PersonalAccessToken();

        $personalAccessToken->setName('Test token');
        $this->assertEquals('Test token', $personalAccessToken->getName());

        $personalAccessToken->setReadOnly(true);
        $this->assertTrue($personalAccessToken->isReadOnly());

        $this->assertNull($personalAccessToken->getRevokedAt());
        $this->assertNull($personalAccessToken->getToken());
    }

    public function testJsonSerialize()
    {
        $personalAccessToken = new PersonalAccessToken('Test token', true);

        $this->assertJsonFixtureEqualsJsonObject('Unit/Resource/personal_access_token.json', $personalAccessToken);
    }
}
