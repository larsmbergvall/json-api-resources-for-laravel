<?php

use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResource;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Author;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Review;

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
