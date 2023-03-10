<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\JsonApi;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use JsonSerializable;

class JsonApiRelationship implements JsonSerializable, Arrayable
{
    public function __construct(public readonly string $name, public readonly Collection|ResourceIdentifierObject|null $related)
    {
    }

    public function toArray(): array
    {
        return [
            $this->name => $this->related ? [
                'data' => $this->related?->jsonSerialize(),
            ] : null,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
