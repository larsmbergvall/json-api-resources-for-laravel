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
        if ($resource['id'] === (string) $id && $resource['type'] === $type) {
            $foundIncluded = true;
            break;
        }
    }

    expect($foundIncluded)->toBeTrue();

    return $this;
});

expect()->extend('toHaveIncludedCount', function (int $expectedCount) {
    $expectation = expect($this->value)->toHaveKey('included');
    $count = count($this->value['included']);

    expect($count)->toEqual($expectedCount);

    return $expectation;
});

expect()->extend('toHaveRelationship', function (string $relationName, int|string $id, string $type) {
    $expectation = expect($this->value)->toHaveKey("relationships.$relationName");

    expect(data_get($this->value, "relationships.$relationName.data"))
        ->toHaveKey('id', $id)
        ->toHaveKey('type', $type);

    return $expectation;
});

expect()->extend('toHaveRelationships', function (string $relationName, array $ids, string $type) {
    $expectation = expect($this->value)->toHaveKey("relationships.$relationName.data");

    $relationships = data_get($this->value, "relationships.$relationName.data");
    $found = 0;

    foreach ($relationships as $relationship) {
        expect($relationship)->toHaveKey('type', $type)
            ->and(in_array($relationship['id'], $ids, false))->toBeTrue();
        $found++;
    }

    expect($found)->toEqual(count($ids));

    return $expectation;
});
