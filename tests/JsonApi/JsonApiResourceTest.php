<?php

use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResource;
use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResponse;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Author;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\AuthorWithHiddenNameAttribute;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\AuthorWithTypeAttribute;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Book;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Review;
use function Pest\Laravel\getJson;

it('transforms id to string', function () {
    $author = Author::factory()->create();

    $jsonResource = JsonApiResource::make($author)->jsonSerialize();

    expect(data_get($jsonResource, 'data.id'))->toBeString();
});

it('does not include id as an attribute if attributes are not specified', function () {
    $author = AuthorWithTypeAttribute::fromBaseFactory();

    $jsonResource = JsonApiResource::make($author)->jsonSerialize();

    expect(data_get($jsonResource, 'data.attributes'))->not->toHaveKey('id');
});

it('has the correct top-level object structure', function () {
    $author = Author::factory()->create();

    $jsonResource = JsonApiResource::make($author)->jsonSerialize();

    expect($jsonResource)->toHaveKey('data')
        ->and($jsonResource)->toHaveKey('links')
        ->and($jsonResource)->toHaveKey('meta');
});

it('transforms empty relationships, links and meta to objects', function () {
    $author = Author::factory()->create();

    $json = json_encode(JsonApiResource::make($author)->jsonSerialize(), JSON_THROW_ON_ERROR);

    expect($json)
        ->toContain('"relationships":{}')
        ->toContain('"links":{}')
        ->toContain('"meta":{}');
});

it('has named keys for relationships', function () {
    $author = Author::factory()->hasBooks(2)->create()->load(['books']);

    $jsonResource = JsonApiResource::make($author)->jsonSerialize();

    expect($relationships = data_get($jsonResource, 'data.relationships'))
        ->toHaveKey('books')
        ->and(data_get($relationships, 'books.data'))
        ->toBeArray()
        ->toHaveCount(2);
});

it('includes loaded relationships', function () {
    $author = Author::factory()->create();
    $review = Review::factory()->recycle($author)->create();

    $author = $author->load(['books.reviews.user.reviews']);

    $jsonResource = JsonApiResource::make($author)->withIncluded()->jsonSerialize();

    expect($jsonResource)
        ->toHaveIncludedCount(3)
        ->toHaveIncludedResource($review->user_id, 'user')
        ->toHaveIncludedResource($review->id, 'review')
        ->toHaveIncludedResource($review->book_id, 'book');
});

it('has null relationships', function () {
    $review = Review::factory()->create(['user_id' => null])->load(['user']);

    $jsonResource = JsonApiResource::make($review)->prepare()->jsonSerialize();

    expect($relationships = data_get($jsonResource, 'data.relationships'))
        ->toHaveKey('user')
        ->and($relationships['user'])->toBeNull();
});

it('respects model hidden property if no includeAttributes attribute is used', function () {
    $author = AuthorWithHiddenNameAttribute::fromBaseFactory();

    $jsonResource = JsonApiResource::make($author)->jsonSerialize();

    expect(data_get($jsonResource, 'data.attributes.name'))->toBeNull();
});

it('has correct content-type header when sent as a response', function () {
    Route::get('/test', fn () => JsonApiResource::make(Book::factory()->create()));

    $response = getJson('/test');
    $response->assertOk();

    $response->assertHeader('Content-Type', JsonApiResponse::JSON_API_CONTENT_TYPE);
});
