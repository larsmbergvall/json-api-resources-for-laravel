<?php

use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResource;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\AuthorWithIncludeAttributesAttribute;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\AuthorWithTypeAttribute;

it('it can rename type via attributes', function () {
    $author = AuthorWithTypeAttribute::fromBaseFactory();

    $jsonArray = JsonApiResource::make($author)->jsonSerialize();

    // If we hadn't specified a type by using the php attribute, it would set the type to 'author_with_type_attribute'
    expect($jsonArray)->toHaveKey('type', 'person');
});

it('it includes fields present in include attributes attribute', function () {
    $author = AuthorWithIncludeAttributesAttribute::fromBaseFactory();

    $jsonArray = JsonApiResource::make($author)->jsonSerialize();

    expect($jsonArray)->toHaveKey('attributes')
        ->and($jsonArray['attributes'])->toHaveCount(2)
        ->toHaveKeys([
            'name',
            'country_of_origin',
        ]);
});
