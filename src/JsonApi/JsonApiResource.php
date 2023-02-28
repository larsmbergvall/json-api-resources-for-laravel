<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\JsonApi;

use ErrorException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonSerializable;
use Larsmbergvall\JsonApiResourcesForLaravel\Attributes\JsonApiIncludeAttributes;
use Larsmbergvall\JsonApiResourcesForLaravel\Attributes\JsonApiIncludeRelationships;
use Larsmbergvall\JsonApiResourcesForLaravel\Attributes\JsonApiType;
use Larsmbergvall\JsonApiResourcesForLaravel\Contracts\JsonApiResourceContract;
use ReflectionClass;

/**
 * @template TModel of Model|JsonApiResourceContract
 */
class JsonApiResource implements JsonSerializable
{
    protected bool $wrap = false;

    protected ReflectionClass $reflectionClass;

    /**
     * @param  TModel  $model
     */
    public function __construct(protected mixed $model)
    {
    }

    public static function make(Model $model): static
    {
        return new static($model);
    }

    public function wrap(): static
    {
        $this->wrap = true;

        return $this;
    }

    public function jsonSerialize(): array
    {
        if (! $this instanceof JsonApiResourceContract) {
            $this->ensureReflectionClassIsCreated();
        }

        $data = [
            'id' => $this->model->id,
            'type' => $this->parseType(),
            'attributes' => $this->parseAttributes(),
            'relationships' => $this->parseRelationships(),
            'links' => [],
            'meta' => [],
        ];

        if ($this->wrap) {
            return ['data' => $data];
        }

        return $data;
    }

    /**
     * @return TModel
     */
    public function modelInstance(): Model|JsonApiResourceContract
    {
        return $this->model;
    }

    /**
     * Attempts to guess what to put in the json objects 'type' property.
     */
    private function parseType(Model|JsonApiResourceContract|null $item = null): string
    {
        if ($item === null) {
            $item = $this->model;
        }

        if ($item instanceof JsonApiResourceContract) {
            return $item->jsonApiType();
        }

        if ($item === $this->model) {
            $this->ensureReflectionClassIsCreated();
            $reflectionClass = $this->reflectionClass;
        } else {
            $reflectionClass = new ReflectionClass($item);
        }

        $phpAttributes = $reflectionClass->getAttributes(JsonApiType::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (empty($phpAttributes)) {
            return Str::snake(class_basename($item::class));
        }

        return $phpAttributes[0]->getArguments()[0];
    }

    /**
     * Returns properties that should be in the attributes object
     *
     * @return array<string, mixed>
     */
    private function parseAttributes(): array
    {
        if ($this->model instanceof JsonApiResourceContract) {
            return $this->model->jsonApiAttributes();
        }

        $this->ensureReflectionClassIsCreated();
        $attributesToInclude = $this->includedAttributes();

        $attributes = [];

        foreach ($this->model->getAttributes() as $property => $value) {
            if (in_array($property, $attributesToInclude)) {
                $attributes[$property] = $value;
            }
        }

        return $attributes;
    }

    /**
     * Returns keys of properties that should be included in the attributes object
     *
     * @return array<int, string>
     */
    private function includedAttributes(): array
    {
        $phpAttributes = $this->reflectionClass->getAttributes(JsonApiIncludeAttributes::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (empty($phpAttributes) && method_exists($this->model, 'getAttributes')) {
            return array_keys($this->model->getAttributes());
        }

        try {
            return $phpAttributes[0]->getArguments()[0];
        } catch (ErrorException $e) {
            return [];
        }
    }

    /**
     * Returns an array of JsonApiRelationship objects to put in the relationships object
     *
     * @return array<int, JsonApiRelationship>
     */
    private function parseRelationships(): array
    {
        if ($this->model instanceof JsonApiResourceContract) {
            return $this->model->jsonApiRelationships();
        }

        $relationshipsToInclude = $this->includedRelationships();
        $relationships = [];

        foreach ($relationshipsToInclude as $relationName) {
            $relatedModelOrCollection = $this->model->{$relationName};

            if ($relatedModelOrCollection instanceof Collection) {
                foreach ($relatedModelOrCollection as $model) {
                    $relationships[] = new JsonApiRelationship($model->id, $this->parseType($model));
                }
            } else {
                $relationships[] = new JsonApiRelationship($relatedModelOrCollection->id, $this->parseType($relatedModelOrCollection));
            }
        }

        return $relationships;
    }

    /**
     * Returns names of relationships that should be included in the relationships object
     *
     * @return array<int, string>
     */
    private function includedRelationships(): array
    {
        $phpAttributes = $this->reflectionClass->getAttributes(JsonApiIncludeRelationships::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (empty($phpAttributes)) {
            return [];
        }

        try {
            return $phpAttributes[0]->getArguments()[0];
        } catch (ErrorException $e) {
            return [];
        }
    }

    private function ensureReflectionClassIsCreated(): void
    {
        if (! isset($this->reflectionClass)) {
            $this->reflectionClass = new ReflectionClass($this->model);
        }
    }
}
