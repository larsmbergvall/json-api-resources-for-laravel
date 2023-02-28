<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Attributes;

use Attribute;

#[Attribute]
class JsonApiIncludeRelationships implements JsonApiAttribute
{
    /**
     * @param  array<int, string>  $relationships
     */
    public function __construct(array $relationships)
    {
    }
}
