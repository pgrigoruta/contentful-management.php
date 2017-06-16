<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit;

use Contentful\Management\ResourceBuilder;

class WebhookHealthTest extends \PHPUnit_Framework_TestCase
{
    public function testJsonSerialize()
    {
        $json = '{"sys":{"type":"Webhook"},"calls":{"total":233,"healthy":102}}';

        $webhookHealth = (new ResourceBuilder())
            ->buildObjectsFromRawData(['sys' => ['type' => 'Webhook'], 'calls' => ['total' => 233, 'healthy' => 102]]);

        $this->assertJsonStringEqualsJsonString($json, json_encode($webhookHealth));
    }
}