<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\Traits;

use PHPUnit\Framework\Assert;

trait TestableJsonApiResource
{
    /**
     * Asserts that this JSON Resource has a given id and type in its main data
     */
    public function assertHasData(int|string $id, string $type): static
    {
        $failMessage = 'Failed to assert that a JsonApiResourceCollection has a JsonApiResource with id: '.$id.' and type: '.$type;

        if (! $this->isPrepared()) {
            $this->type = $this->parseType();
        }

        Assert::assertEquals((string) $id, (string) $this->getId(), $failMessage);
        Assert::assertEquals($type, $this->getType(), $failMessage);

        return $this;
    }
}
