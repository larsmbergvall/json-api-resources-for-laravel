<?php

use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResourceCollection;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Author;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Book;
use Pest\Expectation;

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

    expect($collection)->toHaveIncludedResource($author->books->first()->id, 'book')
        ->toHaveIncludedCount(1)
        ->and($collection['data'][0])
        ->toHaveRelationships('books', [$author->books->first()->id], 'book');
});

it('doesnt include non-loaded relationships', function () {
    /** @var Author $author */
    $author = Author::factory()->hasBooks(3)->create();

    $collection = JsonApiResourceCollection::make(collect([$author->withoutRelations()]))
        ->withIncluded()
        ->jsonSerialize();

    expect($collection)
        ->toHaveKey('included')
        ->and($collection['included'])->toBeEmpty()
        ->and($collection['data'])
        ->each(function (Expectation $expectItem) {
            $expectItem->toHaveKey('relationships')
                ->relationships->not->toBeArray();
        });
});

it('includes nested relations that are loaded', function () {
    $author = Author::factory()->create();
    $book = Book::factory()
        ->for($author)
        ->hasReviews(1)
        ->create();

    $author->load(['books.reviews.user']);

    $collection = JsonApiResourceCollection::make(collect([$author]))
        ->withIncluded()
        ->jsonSerialize();

    expect($collection)
        ->toHaveIncludedCount(3)
        ->toHaveIncludedResource($book->id, 'book')
        ->toHaveIncludedResource($book->reviews->first()->id, 'review')
        ->toHaveIncludedResource($book->reviews->first()->user->id, 'user');
});

it('can create collections from paginators', function () {
    Book::factory()->count(4)->hasReviews(2)->create();

    $books = Book::with([
        'author',
        'reviews',
    ])
        ->paginate(2);

    $collection = JsonApiResourceCollection::make($books);

    expect($collection)->toBeInstanceOf(JsonApiResourceCollection::class)
        ->and($serialized = $collection->jsonSerialize())
        ->toHaveKey('links')
    ->and($serialized['links'])
    ->toHaveKeys([
        // Note: it should not have 'prev' because we are on the first page
        'first',
        'last',
        'next',
    ]);
});
