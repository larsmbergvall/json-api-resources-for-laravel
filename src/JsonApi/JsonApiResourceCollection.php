<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\JsonApi;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use JsonSerializable;

/**
 * @template T of Model
 */
class JsonApiResourceCollection implements JsonSerializable, Arrayable
{
    protected bool $withIncluded = false;

    /** @var class-string<Model> */
    protected string $modelClass;

    /** @var Collection<int, JsonApiResource<T>> */
    private Collection $jsonApiResources;

    /**
     * @param  Collection<int, T>  $models
     * @param  class-string|null  $model
     */
    public function __construct(protected Collection $models, ?string $model = null)
    {
        if (! $model && $this->models->isEmpty()) {
            throw new InvalidArgumentException('There must be either a $model argument or at least one item in $resources!');
        }

        if (! $model) {
            $this->modelClass = $this->models->first()::class;
        } else {
            $this->modelClass = $model;
        }

        /** @var Collection<int, JsonApiResource<T>> $resources */
        $resources = $this->models->map(fn ($resource) => JsonApiResource::make($resource)->withoutWrapping());

        $this->jsonApiResources = $resources;
    }

    /**
     * @param  Collection<int, T>  $models
     * @param  class-string|null  $model
     * @return JsonApiResourceCollection<T>
     */
    public static function make(Collection $models, ?string $model = null): static
    {
        return new static($models, $model);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        $payload = [
            'data' => $this->jsonApiResources->jsonSerialize(),
        ];

        if ($this->withIncluded) {
            $payload['included'] = $this->loadIncluded()->jsonSerialize();
        }

        return $payload;
    }

    public function withIncluded(): static
    {
        $this->withIncluded = true;

        return $this;
    }

    private function loadIncluded(): Collection
    {
        $included = collect();

        foreach ($this->jsonApiResources as $resource) {
            foreach ($this->includedFromResource($resource) as $identifier => $includedItem) {
                $included->put($identifier, $includedItem->withoutWrapping());
            }
        }

        return $included->values();
    }

    /**
     * @return Collection<string, JsonApiResource>
     */
    private function includedFromResource(JsonApiResource $resource): Collection
    {
        return $resource->loadIncluded()->keyBy(fn (JsonApiResource $r) => $r->identifier());
    }
}
