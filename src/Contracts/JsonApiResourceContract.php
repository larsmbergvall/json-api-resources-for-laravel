<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Contracts;

use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiRelationship;

interface JsonApiResourceContract
{
    /**
     * Returns the value to put in the json objects 'type' key
     */
    public function jsonApiType(): string;

    /**
     * Should return an array of attributes, where the key is the name.
     * Example: `['id' => $this->id]`
     *
     * @return array<string, mixed>
     */
    public function jsonApiAttributes(): array;

    /**
     * Should return an array of JsonApiRelationship objects
     *
     * @return array<int, JsonApiRelationship>
     */
    public function jsonApiRelationships(): array;
}
