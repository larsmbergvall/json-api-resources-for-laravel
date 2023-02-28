<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\JsonApi;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class JsonApiRelationship implements JsonSerializable, Arrayable
{
    public function __construct(public int|string $id, public string $type)
    {
    }

    /**
     * @return array{id: int|string, type: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
