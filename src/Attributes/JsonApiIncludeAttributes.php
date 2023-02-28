<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Attributes;

use Attribute;

#[Attribute]
class JsonApiIncludeAttributes implements JsonApiAttribute
{
    /**
     * @param array<int, string> $attributes
     */
    public function __construct(array $attributes)
    {
    }
}
