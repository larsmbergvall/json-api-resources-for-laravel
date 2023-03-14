<?php

use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResource;
use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResponse;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Book;

it('has correct content type', function () {
    expect($r = new JsonApiResponse())
        ->toHaveKey('headers')
        ->and($r->headers->get('Content-Type'))
        ->toEqual('application/vnd.api+json');
});

test('jsonApiResponse helper creates correct response', function () {
    $book = Book::factory()->create();
    $response = jsonApiResponse(JsonApiResource::make($book));

    expect($response->headers->get('Content-Type'))
        ->toEqual('application/vnd.api+json')
        ->and($response->status())->toEqual(200);
});
