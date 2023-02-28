<?php

use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResourceCollection;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Author;

it('collects multiple objects', function () {
    $authors = Author::factory()->count(3)->create();

    $collection = JsonApiResourceCollection::make($authors)->jsonSerialize();

    expect($collection)->toHaveKey('data')
        ->and($collection['data'])
        ->toHaveCount(3)
        ->each
        ->toHaveKey('type', 'author')
        ->toHaveKeys(['id', 'attributes', 'relationships', 'links', 'meta']);
});

it('includes loaded relationships', function () {
    $author = Author::factory()->hasBooks(1)->create();
    $author->load('books');

    $collection = JsonApiResourceCollection::make(collect([$author]))
        ->withIncluded()
        ->jsonSerialize();

    expect($collection)->toHaveKey('included')
        ->and($collection['included'])->toHaveCount(1)
        ->each->toHaveKey('id', $author->books->first()->id)
        ->toHaveKey('type', 'book')
        ->toHaveKeys(['attributes', 'relationships'])
        ->and($collection['data'][0])->toHaveKey('relationships')
        ->and($collection['data'][0]['relationships'])
        ->toHaveCount(1)
        ->each
        ->toHaveKey('id', $author->books->first()->id)
        ->toHaveKey('type', 'book');
});

it('doesnt include non-loaded relationships', function () {
    $authors = Author::factory()->count(3)->hasBooks(3)->create();

    $collection = JsonApiResourceCollection::make($authors)
        ->withIncluded()
        ->jsonSerialize();

    expect($collection)
        ->toHaveKey('included')
        ->and($collection['included'])->toBeEmpty()
        ->and($collection['data'])
        ->each(function (array $item) {
            expect($item)->toHaveKey('relationships')
                ->and($item['relationships'])->toBeEmpty();
        });
});
