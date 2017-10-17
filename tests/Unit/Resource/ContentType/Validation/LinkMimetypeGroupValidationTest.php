<?php

/**
 * This file is part of the contentful-management.php package.
 *
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */
declare(strict_types=1);

namespace Contentful\Tests\Management\Unit\Resource\ContentType\Validation;

use Contentful\Management\Resource\ContentType\Validation\LinkMimetypeGroupValidation;
use Contentful\Tests\Management\BaseTestCase;

class LinkMimetypeGroupValidationTest extends BaseTestCase
{
    public function testJsonSerialize()
    {
        $validation = new LinkMimetypeGroupValidation(['image']);

        $this->assertJsonFixtureEqualsJsonObject('Unit/Resource/ContentType/Validation/link_mimetype_group_validation.json', $validation);
    }

    public function testGetSetData()
    {
        $validation = new LinkMimetypeGroupValidation(['image']);

        $this->assertEquals(['Link'], $validation->getValidFieldTypes());

        $this->assertEquals(['image'], $validation->getMimeTypeGroups());

        $validation->setMimeTypeGroups(['audio', 'video']);
        $this->assertEquals(['audio', 'video'], $validation->getMimeTypeGroups());
    }
}
