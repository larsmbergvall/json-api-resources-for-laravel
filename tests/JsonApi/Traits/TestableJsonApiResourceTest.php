<?php

use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResource;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Book;
use PHPUnit\Framework\AssertionFailedError;

test('assertHasData fails when it does not have expected data', function (bool $expectCorrectId, string $expectedType) {
    $book = Book::factory()->create();

    JsonApiResource::make($book)->assertHasData($expectCorrectId ? $book->id : $book->id + 1, $expectedType);
})->with([
    'wrong id, correct type' => [false, 'book'],
    'correct id, wrong type' => [true, 'wrong_type'],
    'wrong id, wrong type' => [false, 'wrong_type'],
])->expectException(AssertionFailedError::class);

test('assertHasData passes when it has the expected data', function () {
    $book = Book::factory()->create();

    JsonApiResource::make($book)
        ->assertHasData($book->id, 'book');
});

test('assertDoesntHaveData fails when it has the expected data', function () {
    $book = Book::factory()->create();

    JsonApiResource::make($book)->assertDoesntHaveData($book->id, 'book');
})->expectException(AssertionFailedError::class);

test('assertDoesntHaveData passes when it doesnt have the expected data', function (bool $expectCorrectId, string $expectedType) {
    $book = Book::factory()->create();

    JsonApiResource::make($book)->assertDoesntHaveData($expectCorrectId ? $book->id : $book->id + 1, $expectedType);
})->with([
    'wrong id, correct type' => [false, 'book'],
    'correct id, wrong type' => [true, 'wrong_type'],
    'wrong id, wrong type' => [false, 'wrong_type'],
]);
