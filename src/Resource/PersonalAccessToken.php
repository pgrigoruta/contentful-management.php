<?php

/**
 * This file is part of the contentful-management.php package.
 *
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */
declare(strict_types=1);

namespace Contentful\Management\Resource;

use Contentful\Core\Api\DateTimeImmutable;
use Contentful\Management\Resource\Behavior\CreatableInterface;
use function GuzzleHttp\json_encode as guzzle_json_encode;

/**
 * PersonalAccessToken class.
 *
 * This class represents a resource with type "PersonalAccessToken" in Contentful.
 *
 * @see https://www.contentful.com/developers/docs/references/content-management-api/#/reference/personal-access-tokens
 */
class PersonalAccessToken extends BaseResource implements CreatableInterface
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var DateTimeImmutable|null
     */
    protected $revokedAt;

    /**
     * @var bool
     */
    protected $isReadOnly = false;

    /**
     * @var string|null
     */
    protected $token;

    /**
     * PersonalAccessToken constructor.
     *
     * @param string $name
     * @param bool   $isReadOnly
     */
    public function __construct(string $name = '', bool $isReadOnly = false)
    {
        parent::__construct('PersonalAccessToken');
        $this->name = $name;
        $this->isReadOnly = $isReadOnly;
    }

    /**
     * Returns an array to be used by "json_encode" to serialize objects of this class.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'sys' => $this->sys,
            'name' => $this->name,
            'scopes' => $this->isReadOnly
                ? ['content_management_read']
                : ['content_management_manage'],
            'token' => $this->token,
            'revokedAt' => $this->revokedAt ? (string) $this->revokedAt : null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function asRequestBody(): string
    {
        $body = $this->jsonSerialize();

        unset($body['sys']);
        unset($body['token']);
        unset($body['revokedAt']);

        return guzzle_json_encode((object) $body, \JSON_UNESCAPED_UNICODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeadersForCreation(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function asUriParameters(): array
    {
        return [
            'personalAccessToken' => $this->sys->getId(),
        ];
    }

    /**
     * Revokes the personal access token.
     *
     * @see https://www.contentful.com/developers/docs/references/content-management-api/#/reference/personal-access-tokens/token-revoking
     */
    public function revoke()
    {
        return $this->client->requestWithResource($this, 'PUT', '/revoked');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getRevokedAt()
    {
        return $this->revokedAt;
    }

    /**
     * @param bool $isReadOnly
     */
    public function setReadOnly(bool $isReadOnly)
    {
        $this->isReadOnly = $isReadOnly;

        return $this;
    }

    /**
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return $this->isReadOnly;
    }

    /**
     * @return string|null
     */
    public function getToken()
    {
        return $this->token;
    }
}