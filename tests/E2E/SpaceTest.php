<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Exception\NotFoundException;
use Contentful\Link;
use Contentful\Management\Client;
use Contentful\Management\Space;
use Contentful\ResourceArray;

class SpaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->token = getenv('CONTENTFUL_CMA_TEST_TOKEN');
        $this->client = new Client($this->token);
    }

    /**
     * @vcr e2e_space_get.json
     */
    public function testGetSpace()
    {
        $space = $this->client->getSpace('cfexampleapi');

        $this->assertInstanceOf(Space::class, $space);
        $sys = $space->getSystemProperties();
        $this->assertEquals('cfexampleapi', $sys->getId());
        $this->assertEquals('Space', $sys->getType());
        $this->assertEquals(new \DateTimeImmutable('2013-06-23T19:02:00'), $sys->getCreatedAt());
        $this->assertEquals(new \DateTimeImmutable('2016-02-25T09:57:25'), $sys->getUpdatedAt());
        $this->assertEquals(4, $sys->getVersion());
        $this->assertEquals(new Link('7BslKh9TdKGOK41VmLDjFZ', 'User'), $sys->getCreatedBy());
        $this->assertEquals(new Link('7BslKh9TdKGOK41VmLDjFZ', 'User'), $sys->getUpdatedBy());
        $this->assertEquals('Contentful Example API', $space->getName());

        $json = '{"name":"Contentful Example API","sys":{"type":"Space","id":"cfexampleapi","version":4,"createdBy":{"sys":{"type":"Link","linkType":"User","id":"7BslKh9TdKGOK41VmLDjFZ"}},"createdAt":"2013-06-23T19:02:00Z","updatedBy":{"sys":{"type":"Link","linkType":"User","id":"7BslKh9TdKGOK41VmLDjFZ"}},"updatedAt":"2016-02-25T09:57:25Z"}}';
        $this->assertJsonStringEqualsJsonString($json, json_encode($space));
    }

    /**
     * @vcr e2e_space_get_collection.json
     */
    public function testGetSpaces()
    {
        $spaces = $this->client->getSpaces();

        $this->assertInstanceOf(ResourceArray::class, $spaces);
        $this->assertInstanceOf(Space::class, $spaces[0]);
    }

    /**
     * @vcr e2e_space_create_delete_non_english_locale.json
     */
    public function testCreateDeleteSpaceNonEnglishLocale()
    {
        $space = new Space('PHP CMA German Test Space');

        $this->client->createSpace($space, '2vsR8xMXNBmgAuCFdFXG5e', 'de-DE');

        $id = $space->getSystemProperties()->getId();
        $this->assertNotNull($id);

        $this->client->deleteSpace($space);

        try {
            $this->client->getSpace($id);
        } catch (\Exception $e) {
            $this->assertInstanceOf(NotFoundException::class, $e);
        }
    }

    /**
     * @vcr e2e_space_create_update_delete.json
     */
    public function testCreateUpdateDeleteSpace()
    {
        $space = new Space('PHP CMA Test Space');

        $this->client->createSpace($space, '2vsR8xMXNBmgAuCFdFXG5e');

        $id = $space->getSystemProperties()->getId();
        $this->assertNotNull($id);
        $this->assertEquals('PHP CMA Test Space', $space->getName());
        $this->assertEquals(1, $space->getSystemProperties()->getVersion());

        $space->setName('PHP CMA Test Space - Updated');

        $this->client->updateSpace($space);
        $this->assertSame($space, $space);
        $this->assertEquals($id, $space->getSystemProperties()->getId());
        $this->assertEquals('PHP CMA Test Space - Updated', $space->getName());
        $this->assertEquals(2, $space->getSystemProperties()->getVersion());

        $this->client->deleteSpace($space);

        try {
            $this->client->getSpace($id);
        } catch (\Exception $e) {
            $this->assertInstanceOf(NotFoundException::class, $e);
        }
    }
}