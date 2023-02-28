<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Attributes;

use Attribute;

#[Attribute]
class JsonApiType implements JsonApiAttribute
{
    public function __construct(public string $typeName)
    {
    }
}
