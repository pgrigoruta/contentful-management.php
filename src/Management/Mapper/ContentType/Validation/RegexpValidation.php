<?php

/**
 * This file is part of the contentful-management.php package.
 *
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Management\Mapper\ContentType\Validation;

use Contentful\Management\Mapper\BaseMapper;
use Contentful\Management\Resource\ContentType\Validation\RegexpValidation as ResourceClass;

/**
 * RegexpValidation class.
 */
class RegexpValidation extends BaseMapper
{
    /**
     * {@inheritdoc}
     */
    public function map($resource, array $data): ResourceClass
    {
        return $this->hydrate(ResourceClass::class, [
            'pattern' => $data['regexp']['pattern'] ?? null,
            'flags' => $data['regexp']['flags'] ?? null,
        ]);
    }
}