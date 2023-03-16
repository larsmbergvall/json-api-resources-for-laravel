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
        $failMessage = 'Failed to assert that a JsonApiResource contains id: '.$id.' and type: '.$type.' in its main data';

        if (! $this->isPrepared()) {
            $this->type = $this->parseType();
        }

        Assert::assertEquals((string) $id, (string) $this->getId(), $failMessage);
        Assert::assertEquals($type, $this->getType(), $failMessage);

        return $this;
    }

    /**
     * Asserts that this JSON Resource does not have a given id and type in its main data
     */
    public function assertDoesntHaveData(int|string $id, string $type): static
    {
        if (! $this->isPrepared()) {
            $this->type = $this->parseType();
        }

        $hasId = (string) $id === (string) $this->getId();
        $hasType = $type === $this->getType();

        Assert::assertFalse($hasId && $hasType, 'Failed to assert that a JsonApiResource doesnt contain id: '.$id.' and type: '.$type.' in its main data');

        return $this;
    }
}
