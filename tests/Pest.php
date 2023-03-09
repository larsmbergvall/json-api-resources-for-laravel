<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);
uses(LazilyRefreshDatabase::class)->in(__DIR__);

expect()->extend('toHaveIncludedResource', function (int|string $id, string $type) {
    expect($this->value)->toHaveKey('included');

    $included = $this->value['included'];
    $foundIncluded = false;

    foreach ($included as $resource) {
        if ($resource['id'] === $id && $resource['type'] === $type) {
            $foundIncluded = true;
            break;
        }
    }

    expect($foundIncluded)->toBeTrue();

    return $this;
});
