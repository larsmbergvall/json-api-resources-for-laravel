<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\Traits;

use PHPUnit\Framework\Assert;

trait TestableJsonApiResourceCollection
{
    public function assertHasData(int|string $id, string $type): void
    {
        if (! $this->isPrepared()) {
            $this->prepare();
        }

        $found = false;

        foreach ($this->jsonApiResources as $resource) {
            if ((string) $id === (string) $resource->getId() && $type === $resource->getType()) {
                $found = true;
                break;
            }
        }

        Assert::assertTrue(
            $found,
            'Failed to assert that a JsonApiResourceCollection has a JsonApiResource with id: '.$id.' and type: '.$type
        );
    }
}
