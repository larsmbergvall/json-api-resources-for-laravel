<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Traits;

use Illuminate\Support\Str;

trait JsonApiResourceTrait
{
    public function jsonApiType(): string
    {
        return Str::snake(class_basename(__CLASS__));
    }
}
