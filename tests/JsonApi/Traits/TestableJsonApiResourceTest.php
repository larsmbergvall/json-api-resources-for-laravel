<?php

use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResource;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Book;
use PHPUnit\Framework\AssertionFailedError;

it('fails when it does not have expected data', function (bool $expectCorrectId, string $expectedType) {
    $book = Book::factory()->create();

    JsonApiResource::make($book)->assertHasData($expectCorrectId ? $book->id : $book->id + 1, $expectedType);
})->with([
    'wrong id, correct type' => [false, 'book'],
    'correct id, wrong type' => [true, 'wrong_type'],
    'wrong id, wrong type' => [false, 'wrong_type'],
])->expectException(AssertionFailedError::class);

it('passes when it has the expected data', function () {
    $book = Book::factory()->create();

    JsonApiResource::make($book)
        ->assertHasData($book->id, 'book');
});
