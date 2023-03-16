<?php

use Larsmbergvall\JsonApiResourcesForLaravel\Http\Middleware\JsonApiMiddleware;
use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResourceCollection;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Book;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

const JSON_API_CONTENT_TYPE = 'application/vnd.api+json';

it('sets content-type when returning response', function () {
    Route::get('/test', fn () => JsonApiResourceCollection::make(Book::all()))
        ->middleware(JsonApiMiddleware::class);

    Book::factory()->create();
    $response = getJson('/test');
    $response->assertOk();

    $response->assertHeader('Content-Type', JSON_API_CONTENT_TYPE);
});

it('transforms validation errors properly', function () {
    Route::post('/test', function (Illuminate\Http\Request $request) {
        // This will always fail
        $request->validate([
            'data.attributes.title' => ['required', 'min:2'],
            'data.attributes.year' => ['required', 'int'],
        ]);
    })->middleware(JsonApiMiddleware::class);

    $response = postJson('/test', [
        'data' => [
            'id' => 1,
            'type' => 'book',
            'attributes' => [],
        ],
    ]);

    $response->assertHeader('Content-Type', JSON_API_CONTENT_TYPE);
    $data = $response->json();

    expect($data)->toHaveKey('errors')
        ->and($data['errors'])
        ->toHaveCount(2)
        ->each
        ->toHaveKey('status', '422');
});
