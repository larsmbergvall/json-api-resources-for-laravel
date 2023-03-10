<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\JsonApi;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use JsonSerializable;
use T;

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

        $this->jsonApiResources = $this->models->map(fn ($resource) => JsonApiResource::make($resource));
    }

    /**
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
            foreach ($this->includedFromResource($resource->withIncluded()->prepare()) as $identifier => $includedItem) {
                $included->put($identifier, $includedItem);
            }
        }

        return $included->values();
    }

    /**
     * @return Collection<string, JsonApiResource>
     */
    private function includedFromResource(JsonApiResource $resource): Collection
    {
        return $resource->getLoadedIncluded()->keyBy(fn (JsonApiResource $r) => $r->identifier());
        $included = [];

        $model = $resource->modelInstance();

        foreach ($resource->getRelationships() as $relationName => $relationship) {
            // We ignore non-loaded relations
            if (! $model->relationLoaded($relationName)) {
                continue;
            }

            if (is_array($relationship)) {
                foreach ($relationship as $relationshipLinkage) {
                    $relatedModel = $model->{$relationName}->where('id', '=', $relationshipLinkage->id)->first();

                    if (! $relatedModel) {
                        continue;
                    }

                    $identifier = "$relatedModel->type@$relatedModel->id";
                    $included[$identifier] = JsonApiResource::make($relatedModel)->jsonSerialize();
                }

                continue;
            }

            $relatedModel = $model->{$relationName}->where('id', '=', $relationship->id)->first();

            if (! $relatedModel) {
                continue;
            }

            $identifier = "$relationship->type@$relationship->id";
            $included[$identifier] = JsonApiResource::make($relatedModel)->jsonSerialize();
        }

        return $included;
    }
}
