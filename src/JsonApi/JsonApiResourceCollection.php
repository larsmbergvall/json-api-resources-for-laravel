<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\JsonApi;

use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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

    protected Collection $models;

    protected ?PaginatorContract $paginator = null;

    protected array $links;

    /** @var Collection<int, JsonApiResource<T>> */
    private Collection $jsonApiResources;

    /**
     * @param  Paginator<T>|PaginatorContract<T>|Collection<int, T>  $models
     * @param  class-string|null  $model
     */
    public function __construct(Collection|Paginator|PaginatorContract $models, ?string $model = null)
    {
        if (! $model && $models->isEmpty()) {
            throw new InvalidArgumentException('There must be either a $model argument or at least one item in $resources!');
        }

        if ($models instanceof PaginatorContract) {
            $this->models = collect($models->items());
            $this->paginator = $models;
        } else {
            $this->models = $models;
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
     * @param  Paginator<T>|PaginatorContract<T>|Collection<int, T>  $models
     * @param  class-string|null  $model
     * @return JsonApiResourceCollection<T>
     */
    public static function make(Collection|Paginator|PaginatorContract $models, ?string $model = null): static
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

        if ($this->paginator) {
            $payload['links'] = $this->linksFromPaginator($this->paginator);
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

    private function linksFromPaginator(Paginator|PaginatorContract $paginator): array
    {
        /** @phpstan-ignore-next-line */
        $serializedPaginator = $paginator->jsonSerialize();

        return array_map(fn (string $link) => Str::replace('\\/', '/', $link), array_filter([
            'first' => data_get($serializedPaginator, 'first_page_url'),
            'last' => data_get($serializedPaginator, 'last_page_url'),
            'prev' => data_get($serializedPaginator, 'prev_page_url'),
            'next' => data_get($serializedPaginator, 'next_page_url'),
        ]));
    }
}
