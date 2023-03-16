<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\JsonApi;

use JsonSerializable;

class JsonApiError implements JsonSerializable
{
    public function __construct(public readonly int $status, public readonly string $title, public readonly string $detail, public readonly JsonApiErrorSource $source)
    {
    }

    public function jsonSerialize(): array
    {
        return [
            'status' => (string) $this->status,
            'title' => $this->title,
            'detail' => $this->detail,
            'source' => $this->source->jsonSerialize(),
        ];
    }
}
