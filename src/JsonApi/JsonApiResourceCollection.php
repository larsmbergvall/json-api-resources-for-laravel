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
            'data' => $this->jsonApiResources->jsonSerialize(),
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
            foreach ($this->includedFromResource($resource) as $identifier => $includedItem) {
                $included[$identifier] = $includedItem;
            }
        }

        return array_values($included);
    }

    private function includedFromResource(JsonApiResource $resource): array
    {
        $included = [];

        $model = $resource->modelInstance();

        foreach ($resource->getRelationships() as $relationName => $relationship) {
            // We ignore non-loaded relations
            if (! $model->relationLoaded($relationName)) {
                ray('skipping '.$relationName);

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
