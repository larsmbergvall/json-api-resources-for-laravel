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
use ReflectionClass;
use ReflectionException;

/**
 * @template TModel of Model
 */
class JsonApiResource implements JsonSerializable
{
    protected bool $wrap = true;

    protected bool $withIncluded = false;

    protected ReflectionClass $reflectionClass;

    protected string $type;

    /** @var array<string, mixed> */
    protected array $attributes;

    /** @var Collection<string, JsonApiRelationship> */
    protected Collection $relationships;

    protected Collection $loadedIncluded;

    /**
     * @param  TModel  $model
     */
    public function __construct(protected Model $model)
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

    public function withoutWrapping(): static
    {
        $this->wrap = false;

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
            'id' => (string) $this->model->id,
            'type' => $this->type,
            'attributes' => $this->attributes,
            /** @phpstan-ignore-next-line (Yes, it does exist in this case) */
            'relationships' => $this->relationships->isEmpty() ? (object) [] : $this->relationships->jsonSerialize()[0],
        ];

        if ($this->wrap) {
            $data = ['data' => $data];
        }

        if ($this->withIncluded) {
            $data['included'] = $this->loadedIncluded->jsonSerialize();
        }

        $data['links'] = (object) [];
        $data['meta'] = (object) [];

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

    /** @returns Collection<int, JsonApiRelationship> */
    public function getRelationships(): Collection
    {
        return $this->relationships;
    }

    public function getLoadedIncluded(): Collection
    {
        return $this->loadedIncluded;
    }

    /**
     * @return TModel
     */
    public function modelInstance(): Model
    {
        return $this->model;
    }

    public function isPrepared(): bool
    {
        return isset($this->type, $this->attributes, $this->relationships, $this->loadedIncluded);
    }

    /**
     * Parses attributes and relationships to include, loads included models and turns them into resources. This
     * method sets this objects type, attributes, relationships and loadedIncluded properties and should be
     * used before JsonSerializing it
     *
     * @throws ReflectionException
     */
    public function prepare(): self
    {
        $this->ensureReflectionClassIsCreated();

        $this->type = $this->parseType();
        $this->attributes = $this->parseAttributes();
        $this->relationships = $this->parseRelationships();

        if ($this->withIncluded) {
            $this->loadedIncluded = $this->loadIncluded($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, JsonApiResource>
     */
    public function loadIncluded(JsonApiResource $resource = null, array &$alreadyIncludedIdentifiers = []): Collection
    {
        $included = collect();

        if ($resource === null) {
            $resource = $this;
        }

        foreach ($resource->relationships as $relationData) {
            /** @var Model|Collection<int, Model>|null $related */
            $related = $resource->modelInstance()->{$relationData->name};

            if (! $related) {
                continue;
            }

            if ($related instanceof Collection) {
                foreach ($related as $relatedModel) {
                    $resource = self::make($relatedModel)->prepare();

                    if (in_array($resource->identifier(), $alreadyIncludedIdentifiers, true)) {
                        continue;
                    }

                    $alreadyIncludedIdentifiers[] = $resource->identifier();
                    // Included items should not be wrapped in a 'data' prop
                    $included->push($resource->withoutWrapping());
                    $included = $included->merge($this->loadIncluded($resource, $alreadyIncludedIdentifiers));
                }
            } else {
                $resource = self::make($related)->prepare();

                if (in_array($resource->identifier(), $alreadyIncludedIdentifiers, true)) {
                    continue;
                }

                $alreadyIncludedIdentifiers[] = $resource->identifier();
                // Included items should not be wrapped in a 'data' prop
                $included->push($resource->withoutWrapping());

                $included = $included->merge($this->loadIncluded($resource, $alreadyIncludedIdentifiers));
            }
        }

        return $included;
    }

    /**
     * Attempts to guess what to put in the json objects 'type' property.
     */
    protected function parseType(Model|null $item = null): string
    {
        if ($item === null) {
            $item = $this->model;
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
     *
     * @throws ReflectionException
     */
    protected function parseAttributes(): array
    {
        $this->ensureReflectionClassIsCreated();
        $attributesToInclude = $this->includedAttributes();

        $attributes = [];

        foreach ($this->model->getAttributes() as $property => $value) {
            if (in_array($property, $attributesToInclude, true)) {
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
    protected function includedAttributes(): array
    {
        $phpAttributes = $this->reflectionClass->getAttributes(JsonApiIncludeAttributes::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (empty($phpAttributes) && method_exists($this->model, 'attributesToArray')) {
            $attributes = array_keys($this->model->attributesToArray());

            return array_filter($attributes, fn (string $key) => $key !== 'id');
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
     * @return Collection<string, JsonApiRelationship>
     */
    protected function parseRelationships(): Collection
    {
        $relationshipsToInclude = $this->includedRelationships();
        $relationships = collect();

        foreach ($relationshipsToInclude as $relationName) {
            // We ignore non-loaded relations
            if (! $this->model->relationLoaded($relationName)) {
                continue;
            }

            $relatedModelOrCollection = $this->model->{$relationName};

            if (! $relatedModelOrCollection) {
                $resourceIdentifiers = null;
            } elseif ($relatedModelOrCollection instanceof Collection) {
                $resourceIdentifiers = $relatedModelOrCollection
                    ->map(fn (Model $m) => new ResourceIdentifierObject($m->id, $this->parseType($m)));
            } else {
                $resourceIdentifiers = new ResourceIdentifierObject($relatedModelOrCollection->id, $this->parseType($relatedModelOrCollection));
            }

            $relationships->push(new JsonApiRelationship($relationName, $resourceIdentifiers));
        }

        return $relationships;
    }

    /**
     * Returns names of relationships that should be included in the relationships object
     *
     * @return array<int, string>
     */
    protected function includedRelationships(): array
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
     * @throws ReflectionException
     */
    protected function ensureReflectionClassIsCreated(): void
    {
        if (! isset($this->reflectionClass)) {
            $this->reflectionClass = new ReflectionClass($this->model);
        }
    }
}
