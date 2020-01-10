<?php

/**
 * This file is part of the contentful/contentful-management package.
 *
 * @copyright 2015-2020 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Management\SystemProperties;

class Organization extends BaseSystemProperties
{
    use Component\CreatedAtTrait;
    use
        Component\UpdatedAtTrait;
    use
        Component\VersionedTrait;

    /**
     * Organization constructor.
     */
    public function __construct(array $sys)
    {
        $this->init('Organization', $sys['id'] ?? '');

        $this->initCreatedAt($sys);
        $this->initUpdatedAt($sys);
        $this->initVersioned($sys);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return \array_merge(
            parent::jsonSerialize(),
            $this->jsonSerializeCreatedAt(),
            $this->jsonSerializeUpdatedAt(),
            $this->jsonSerializeVersioned()
        );
    }
}
