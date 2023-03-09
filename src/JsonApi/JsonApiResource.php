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

    protected bool $withIncluded = false;

    protected ReflectionClass $reflectionClass;

    protected string $type;

    /** @var array<string, mixed> */
    protected array $attributes;

    /** @var array<string, JsonApiRelationship> */
    protected array $relationships;

    protected Collection $loadedIncluded;

    /**
     * @param  TModel|JsonApiResourceContract  $model
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

    public function withIncluded(): static
    {
        $this->withIncluded = true;

        return $this;
    }

    public function jsonSerialize(): array
    {
        if (! $this->isPrepared()) {
            $this->prepare();
        }

        $data = [
            'id' => $this->model->id,
            'type' => $this->type,
            'attributes' => $this->attributes,
            'relationships' => $this->relationships,
            'links' => [],
            'meta' => [],
        ];

        if ($this->wrap || $this->withIncluded) {
            $data = ['data' => $data];
        }

        if ($this->withIncluded) {
            $data['included'] = $this->loadedIncluded->jsonSerialize();
        }

        return $data;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function identifier(): string
    {
        return $this->getType().'@'.$this->modelInstance()->id;
    }

    /** @returns array<string, mixed> */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /** @returns array<string, JsonApiRelationship> */
    public function getRelationships(): array
    {
        return $this->relationships;
    }

    /**
     * @return TModel
     */
    public function modelInstance(): Model|JsonApiResourceContract
    {
        return $this->model;
    }

    public function isPrepared(): bool
    {
        return isset($this->type, $this->attributes, $this->relationships, $this->loadedIncluded);
    }

    /**
     * Parses attributes and relationships to include, loads included models and turns them into resources.
     */
    public function prepare(): self
    {
        if (! $this->model instanceof JsonApiResourceContract) {
            $this->ensureReflectionClassIsCreated();
        }

        $this->type = $this->parseType();
        $this->attributes = $this->parseAttributes();
        $this->relationships = $this->parseRelationships();

        if ($this->withIncluded) {
            $this->loadedIncluded = $this->loadIncluded($this);
        }

        return $this;
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
     * Returns an array of JsonApiRelationship objects to put in the relationships object.
     * The keys in this array should be the name of the relationship
     *
     * @return array<string, JsonApiRelationship>
     */
    private function parseRelationships(): array
    {
        if ($this->model instanceof JsonApiResourceContract) {
            return $this->model->jsonApiRelationships();
        }

        $relationshipsToInclude = $this->includedRelationships();
        $relationships = [];

        foreach ($relationshipsToInclude as $relationName) {
            // We ignore non-loaded relations
            if (! $this->model->relationLoaded($relationName)) {
                continue;
            }

            $relatedModelOrCollection = $this->model->{$relationName};

            if ($relatedModelOrCollection instanceof Collection) {
                $relationships[$relationName] = [];

                foreach ($relatedModelOrCollection as $model) {
                    $relationships[$relationName][] = new JsonApiRelationship($model->id, $this->parseType($model));
                }
            } else {
                $relationships[$relationName] = new JsonApiRelationship($relatedModelOrCollection->id, $this->parseType($relatedModelOrCollection));
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

    /**
     * @return Collection<int, JsonApiResource>
     */
    private function loadIncluded(JsonApiResource $resource, array &$alreadyIncludedIdentifiers = []): Collection
    {
        $included = collect();

        foreach ($resource->relationships as $relationName => $relationData) {
            /** @var Model|Collection<int, Model> $related */
            $related = $resource->modelInstance()->{$relationName};

            if ($related instanceof Collection) {
                foreach ($related as $relatedModel) {
                    $resource = self::make($relatedModel)->prepare();

                    if (in_array($resource->identifier(), $alreadyIncludedIdentifiers, true)) {
                        continue;
                    }

                    $alreadyIncludedIdentifiers[] = $resource->identifier();
                    $included->push($resource);
                    $included = $included->merge($this->loadIncluded($resource, $alreadyIncludedIdentifiers));
                }
            } else {
                $resource = self::make($related)->prepare();

                if (in_array($resource->identifier(), $alreadyIncludedIdentifiers, true)) {
                    continue;
                }

                $alreadyIncludedIdentifiers[] = $resource->identifier();
                $included->push($resource);

                $included = $included->merge($this->loadIncluded($resource, $alreadyIncludedIdentifiers));
            }
        }

        return $included;
    }

    private function _loadIncluded(Model $relatedModel): Collection
    {
        $included = collect();

        $resource = self::make($relatedModel)->prepare();
        $identifier = $resource->identifier();
        $included->put($identifier, $resource);

        $included = $included->merge($this->loadIncluded($resource));
    }

    private function ensureReflectionClassIsCreated(): void
    {
        if (! isset($this->reflectionClass)) {
            $this->reflectionClass = new ReflectionClass($this->model);
        }
    }
}