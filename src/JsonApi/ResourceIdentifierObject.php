<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\JsonApi;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class ResourceIdentifierObject implements JsonSerializable, Arrayable
{
    public function __construct(public readonly int|string $id, public readonly string $type)
    {
    }

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
