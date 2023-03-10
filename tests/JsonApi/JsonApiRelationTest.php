<?php

use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiRelationship;
use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\ResourceIdentifierObject;

it('has correct structure for one-to-one relations', function () {
    $resourceIdentifier = new ResourceIdentifierObject(1, 'book');

    $relationship = (new JsonApiRelationship('book', $resourceIdentifier))->jsonSerialize();

    expect($relationship)
        ->toHaveKey('book')
        ->and(data_get($relationship, 'book.data'))
        ->toHaveKey('id', 1)
        ->toHaveKey('type', 'book');
});

it('has correct structure for one-to-many relations', function () {
    $resourceIdentifier = new ResourceIdentifierObject(1, 'book');
    $resourceIdentifier2 = new ResourceIdentifierObject(2, 'book');

    $relationship = (new JsonApiRelationship('books', collect([$resourceIdentifier, $resourceIdentifier2])))->jsonSerialize();

    expect($relationship)
        ->toHaveKey('books.data')
        ->and(data_get($relationship, 'books.data'))
        ->toHaveCount(2)
        ->toContain(['id' => 1, 'type' => 'book'], ['id' => 2, 'type' => 'book']);
});
