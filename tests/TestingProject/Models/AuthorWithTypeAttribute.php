<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models;

use Larsmbergvall\JsonApiResourcesForLaravel\Attributes\JsonApiType;

#[JsonApiType('person')]
class AuthorWithTypeAttribute extends Author
{
    use CreateFromBaseFactory;
}
