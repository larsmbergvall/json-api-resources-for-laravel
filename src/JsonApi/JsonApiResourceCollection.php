<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\JsonApi;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use JsonSerializable;
use Larsmbergvall\JsonApiResourcesForLaravel\Contracts\JsonApiResourceContract;

/**
 * @template T of Model|JsonApiResourceContract
 */
class JsonApiResourceCollection implements JsonSerializable, Arrayable
{
    protected bool $withIncluded = false;

    /** @var class-string<Model|JsonApiResourceContract> */
    protected string $modelClass;

    /** @var Collection<int, JsonApiResource<T>> */
    private Collection $jsonApiResources;

    /**
     * @param  Collection<int, T>  $resources
     * @param  class-string|null  $model
     */
    public function __construct(protected Collection $resources, ?string $model = null)
    {
        if (! $model && $this->resources->isEmpty()) {
            throw new InvalidArgumentException('There must be either a $model argument or at least one item in $resources!');
        }

        if (! $model) {
            $this->modelClass = $this->resources->first()::class;
        } else {
            $this->modelClass = $model;
        }

        $this->jsonApiResources = $this->resources->map(fn ($resource) => JsonApiResource::make($resource));
        $this->resources = $this->resources->keyBy('id');
    }

    public static function make(Collection $resources): static
    {
        return new static($resources);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        $payload = [
            'data' => $this->resources->map(fn ($resource) => JsonApiResource::make($resource)->jsonSerialize())->values()->toArray(),
        ];

        if ($this->withIncluded) {
            $payload['included'] = $this->included();
        }

        return $payload;
    }

    public function withIncluded(): static
    {
        $this->withIncluded = true;

        return $this;
    }

    private function included(): array
    {
        $included = [];

        foreach ($this->jsonApiResources as $resource) {
            $model = $resource->modelInstance();

            // TODO: Loop over all relationships in $resource and push any loaded ones into $included
            $identifier = $model::class.'@'.$model->id;

//            $included[$identifier] = $
        }

        return $included;
    }
}
