<?php

/**
 * This file is part of the contentful-management.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */
declare(strict_types=1);

namespace Contentful\Management;

use Contentful\Core\Api\BaseClient;
use Contentful\Core\Api\Link;
use Contentful\Core\Resource\ResourceArray;
use Contentful\Management\Resource\Behavior\CreatableInterface;
use Contentful\Management\Resource\ResourceInterface;

/**
 * Client class.
 *
 * This class is responsible for querying Contentful's Content Management API.
 */
class Client extends BaseClient
{
    use ClientExtension\OrganizationExtension,
        ClientExtension\SpaceExtension,
        ClientExtension\UserExtension;

    /**
     * The current version of the SDK.
     *
     * @var string
     */
    const VERSION = '1.1.0-dev';

    /**
     * The default URI to which all requests should be made.
     *
     * @var string
     */
    const URI_MANAGEMENT = 'https://api.contentful.com';

    /**
     * The special URI for uploading files.
     *
     * @var string
     */
    const URI_UPLOAD = 'https://upload.contentful.com';

    /**
     * A ResourceBuilder instance,
     * which is responsible for converting API responses into PHP objects.
     *
     * @var ResourceBuilder
     */
    private $builder;

    /**
     * An instance of the configuration class that is used for handling API calls.
     *
     * @var ApiConfiguration
     */
    private $configuration;

    /**
     * Client constructor.
     *
     * @param string $accessToken A OAuth token or personal access token generated by Contentful
     * @param array  $options     An array of options, with the following supported values:
     *                            * guzzle: an instance of the Guzzle client
     *                            * logger: a PSR-3 logger
     *                            * baseUri: a string that will replace the default Contentful URI
     */
    public function __construct(string $accessToken, array $options = [])
    {
        parent::__construct(
            $accessToken,
            $options['baseUri'] ?? self::URI_MANAGEMENT,
            $options['logger'] ?? null,
            $options['guzzle'] ?? null
        );

        $this->builder = new ResourceBuilder();
        $this->configuration = new ApiConfiguration();
    }

    /**
     * Returns the active ResourceBuilder instance.
     *
     * @return ResourceBuilder
     */
    public function getBuilder(): ResourceBuilder
    {
        return $this->builder;
    }

    /**
     * @param string                 $method   The HTTP method
     * @param string                 $path     The URI path
     * @param array                  $options  An array of optional parameters. The following keys are accepted:
     *                                         * query   An array of query parameters that will be appended to the URI
     *                                         * headers An array of headers that will be added to the request
     *                                         * body    The request body
     *                                         * baseUri A string that can be used to override the default client base URI
     * @param ResourceInterface|null $resource Optionally, a resource whose properties will be overwritten
     *
     * @return ResourceInterface|ResourceArray|null
     */
    public function makeRequest(string $method, string $path, array $options = [], ResourceInterface $resource = null)
    {
        $path = \rtrim($path, '/');

        $response = $this->request($method, $path, $options);

        if ($response) {
            $resource = $this->builder->build($response, $resource);
        }

        if ($resource) {
            // If it's not an instance of ResourceInterface,
            // it's an instance of ResourceArray
            foreach ($resource instanceof ResourceArray ? $resource : [$resource] as $resourceObject) {
                $resourceObject->setClient($this);
            }
        }

        return $resource;
    }

    /**
     * Persists the current resource in the given scope.
     * You can use this method in 2 ways.
     *
     * Creating using an actual resource object
     * ``` php
     * // $environment is an instance of Contentful\Management\Resource\Environment
     * $client->create($entry, $environment);
     * ```
     *
     * Creating using an array with the required IDs
     * ``` php
     * $client->create($entry, $entryCustomId, ['space' => $spaceId, 'environment' => $environmentId]);
     * ```
     *
     * @param CreatableInterface         $resource   The resource that needs to be created in Contentful
     * @param string                     $resourceId If this parameter is specified, the SDK will attempt
     *                                               to create a resource by making a PUT request on the endpoint
     *                                               by also specifying the ID
     * @param ResourceInterface|string[] $parameters Either an actual resource object,
     *                                               or an array containing the required IDs
     */
    public function create(CreatableInterface $resource, string $resourceId = '', $parameters = [])
    {
        if ($parameters instanceof ResourceInterface) {
            $parameters = $parameters->asUriParameters();
        }

        $config = $this->configuration->getConfigFor($resource);
        $uri = $this->buildRequestUri($config, $parameters, $resourceId);

        $this->makeRequest($resourceId ? 'PUT' : 'POST', $uri, [
            'body' => $resource->asRequestBody(),
            'headers' => $resource->getHeadersForCreation(),
            'baseUri' => $config['baseUri'] ?? null,
        ], $resource);
    }

    /**
     * Make an API request using the given resource.
     * The object will be used to infer the API endpoint.
     *
     * @param ResourceInterface $resource        An SDK resource object
     * @param string            $method          The HTTP method
     * @param string            $path            Optionally, a path to be added at the of the URI (like "/published")
     * @param array             $options         An array of valid options (baseUri, body, headers)
     * @param bool              $hydrateResource Whether to update the given resource using the result of the API call
     *
     * @return ResourceInterface|ResourceArray|null
     */
    public function requestWithResource(
        ResourceInterface $resource,
        string $method,
        string $path = '',
        array $options = [],
        bool $hydrateResource = true
    ) {
        $config = $this->configuration->getConfigFor($resource);
        $uri = $this->buildRequestUri($config, $resource->asUriParameters());

        $options['baseUri'] = $config['baseUri'] ?? null;
        $targetResource = $hydrateResource ? $resource : null;

        return $this->makeRequest($method, $uri.$path, $options, $targetResource);
    }

    /**
     * @param string                 $class
     * @param string[]               $parameters
     * @param Query|null             $query
     * @param ResourceInterface|null $resource
     *
     * @return ResourceInterface|ResourceArray
     */
    public function fetchResource(
        string $class,
        array $parameters,
        Query $query = null,
        ResourceInterface $resource = null
    ) {
        $config = $this->configuration->getConfigFor($class);
        $uri = $this->buildRequestUri($config, $parameters);

        return $this->makeRequest('GET', $uri, [
            'baseUri' => $config['baseUri'] ?? null,
            'query' => $query ? $query->getQueryData() : [],
        ], $resource);
    }

    /**
     * Resolves a link to a Contentful resource.
     *
     * @param Link     $link
     * @param string[] $parameters
     *
     * @return ResourceInterface
     */
    public function resolveLink(Link $link, array $parameters = []): ResourceInterface
    {
        $config = $this->configuration->getLinkConfigFor($link->getLinkType());
        $uri = $this->buildRequestUri($config, $parameters, $link->getId());

        return $this->makeRequest('GET', $uri, [
            'baseUri' => $config['baseUri'] ?? null,
        ]);
    }

    /**
     * Given a configuration array an a set of parameters,
     * builds the URI that identifies the current request.
     *
     * @param array    $config
     * @param string[] $parameters
     * @param string   $resourceId
     *
     * @return string
     */
    private function buildRequestUri(array $config, array $parameters, string $resourceId = ''): string
    {
        $idParameter = $config['id'];
        $parameters[$idParameter] = $parameters[$idParameter] ?? $resourceId;

        $parameters = $this->validateParameters(
            $config['parameters'],
            $parameters,
            $idParameter,
            $config['class']
        );

        $replacements = [];
        foreach ($parameters as $key => $value) {
            $replacements['{'.$key.'}'] = $value;
        }

        return \strtr($config['uri'], $replacements);
    }

    /**
     * Validates given parameters for the API request,
     * and throws an exception if they are not correctly set.
     *
     * @param string[] $required    The parameters required from the configuration of a certain endpoint
     * @param string[] $current     The parameters supplied to the current query
     * @param string   $idParameter The name of the parameter that identifies the resource ID
     * @param string   $class       The resource class
     *
     * @throws \RuntimeException When some parameters are missing
     *
     * @return string[]
     */
    private function validateParameters(array $required, array $current, string $idParameter, string $class): array
    {
        $missing = [];
        $valid = [];
        foreach ($required as $parameter) {
            if (!isset($current[$parameter])) {
                $missing[] = $parameter;

                continue;
            }

            $valid[$parameter] = $current[$parameter];
        }

        if ($missing) {
            throw new \RuntimeException(\sprintf(
                'Trying to make an API call on resource of class "%s" without required parameters "%s".',
                $class,
                \implode(', ', $missing)
            ));
        }

        if ($idParameter && isset($current[$idParameter])) {
            $valid[$idParameter] = $current[$idParameter];
        }

        return $valid;
    }

    /**
     * {@inheritdoc}
     */
    public function getApi()
    {
        return 'MANAGEMENT';
    }

    /**
     * {@inheritdoc}
     */
    protected function getExceptionNamespace()
    {
        return __NAMESPACE__.'\\Exception';
    }

    /**
     * {@inheritdoc}
     */
    protected function getSdkName(): string
    {
        return 'contentful-management.php';
    }

    /**
     * {@inheritdoc}
     */
    protected function getSdkVersion(): string
    {
        return self::VERSION;
    }

    /**
     * {@inheritdoc}
     */
    protected function getApiContentType(): string
    {
        return 'application/vnd.contentful.management.v1+json';
    }
}
