<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Attributes;

use Attribute;

#[Attribute]
class JsonApiIncludeAttributes implements JsonApiAttribute
{
    /**
     * @param  array<int, string>  $attributes
     */
    /* @phpstan-ignore-next-line */
    public function __construct(array $attributes)
    {
    }
}
