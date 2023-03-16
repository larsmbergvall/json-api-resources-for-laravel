<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\JsonApi;

use JsonSerializable;

class JsonApiErrorSource implements JsonSerializable
{
    public function __construct(public readonly string $pointer, public readonly string $parameter)
    {
    }

    /**
     * @return array{pointer: string, parameter: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'pointer' => $this->pointer,
            'parameter' => $this->parameter,
        ];
    }
}
