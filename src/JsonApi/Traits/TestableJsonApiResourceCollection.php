<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\Traits;

use PHPUnit\Framework\Assert;

trait TestableJsonApiResourceCollection
{
    public function assertHasData(int|string $id, string $type): static
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

        return $this;
    }

    public function assertDoesntHaveData(int|string $id, string $type): static
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

        Assert::assertFalse($found, 'Failed to assert that a JsonApiResourceCollection doesnt contain a resource with id: '.$id.' and type: '.$type.' in its main data');

        return $this;
    }
}
