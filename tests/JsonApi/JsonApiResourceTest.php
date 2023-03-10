<?php

use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResource;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Author;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\AuthorWithTypeAttribute;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Review;

it('transforms id to string', function () {
    $author = Author::factory()->create();

    $jsonResource = JsonApiResource::make($author)->jsonSerialize();

    expect($jsonResource['id'])->toBeString();
});

it('does not include id as an attribute if attributes are not specified', function () {
    $author = AuthorWithTypeAttribute::fromBaseFactory();

    $jsonResource = JsonApiResource::make($author)->jsonSerialize();

    expect(data_get($jsonResource, 'data.attributes'))->not->toHaveKey('id');
});

it('has the correct object structure', function () {
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

it('includes loaded relationships', function () {
    $author = Author::factory()->create();
    $review = Review::factory()->recycle($author)->create();

    $author = $author->load(['books.reviews.user.reviews']);

    $jsonResource = JsonApiResource::make($author)->prepare()->wrap()->withIncluded()->jsonSerialize();

    expect($jsonResource)
        ->toHaveIncludedResource($review->user_id, 'user')
        ->toHaveIncludedResource($review->id, 'review')
        ->toHaveIncludedResource($review->book_id, 'book');
});
