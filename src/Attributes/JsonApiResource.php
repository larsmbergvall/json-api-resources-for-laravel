<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Attributes;

use Attribute;

#[Attribute]
class JsonApiResource
{
    /**
     * @param class-string $jsonApiResourceClass
     */
    public function __construct(public string $jsonApiResourceClass)
    {
    }
}
