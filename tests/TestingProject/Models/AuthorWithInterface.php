<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models;

use Larsmbergvall\JsonApiResourcesForLaravel\Contracts\JsonApiResourceContract;

class AuthorWithInterface extends Author implements JsonApiResourceContract
{
    use CreateFromBaseFactory;

    public function jsonApiType(): string
    {
        return 'author_from_interface';
    }

    public function jsonApiAttributes(): array
    {
        return [
            'id',
            'name',
            'country_of_origin',
            'bio',
        ];
    }
}
