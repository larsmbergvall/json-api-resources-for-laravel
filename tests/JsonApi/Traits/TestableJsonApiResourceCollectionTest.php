<?php

use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResourceCollection;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Book;
use PHPUnit\Framework\AssertionFailedError;

it('fails when it does not have expected data', function (bool $expectCorrectId, string $expectedType, bool $withPaginator) {
    $books = Book::factory()->count(2)->create();

    if ($withPaginator) {
        $books = Book::paginate();
    }

    JsonApiResourceCollection::make($books)
        ->assertHasData($expectCorrectId ? $books->first()->id : $books->first()->id + 100, $expectedType);
})->with([
    'wrong id, correct type' => [false, 'book'],
    'correct id, wrong type' => [true, 'wrong_type'],
    'wrong id, wrong type' => [false, 'wrong_type'],
])->with([
    'with paginator' => [true],
    'without paginator' => [false],
])
    ->expectException(AssertionFailedError::class);

it('passes when it has the expected data', function () {
    $books = Book::factory()->count(2)->create();

    JsonApiResourceCollection::make($books)
        ->assertHasData($books->first()->id, 'book');
})->with([
    'with paginator' => [true],
    'without paginator' => [false],
]);
