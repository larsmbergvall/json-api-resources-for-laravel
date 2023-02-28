<?php

use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResource;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\AuthorWithInterface;

it('can rename type via jsonApiType method', function () {
    $author = AuthorWithInterface::fromBaseFactory();

    $resource = JsonApiResource::make($author)->jsonSerialize();

    expect($resource)->toHaveKey('type', 'author_from_interface');
});

it('gets attributes from jsonApiAttributes method', function () {
    $author = AuthorWithInterface::fromBaseFactory();

    $resource = JsonApiResource::make($author)->jsonSerialize();

    expect($resource['attributes'])
        ->toHaveCount(4)
        ->toContain(
            'id',
            'name',
            'country_of_origin',
            'bio',
        );
});
