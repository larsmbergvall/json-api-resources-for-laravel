<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models;

use Larsmbergvall\JsonApiResourcesForLaravel\Attributes\JsonApiIncludeAttributes;

#[JsonApiIncludeAttributes([
    'name',
    'country_of_origin',
])]
class AuthorWithIncludeAttributesAttribute extends Author
{
    use CreateFromBaseFactory;
}
