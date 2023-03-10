<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Attributes;

use Attribute;

#[Attribute]
class JsonApiIncludeRelationships implements JsonApiAttribute
{
    /**
     * @param  array<int, string>  $relationships
     */
    /** @phpstan-ignore-next-line  */
    public function __construct(array $relationships)
    {
    }
}
