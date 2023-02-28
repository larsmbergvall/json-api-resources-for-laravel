<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models;

trait CreateFromBaseFactory
{
    /**
     * This is used so that we can easily create models from the "base factory". In other words, we can reuse AuthorFactory
     * for all Author models, i.e. AuthorWithInterface can still utilize the same factory.
     */
    public static function fromBaseFactory(array $attributes = []): static
    {
        return static::create(static::factory()->make($attributes)->attributesToArray());
    }
}
