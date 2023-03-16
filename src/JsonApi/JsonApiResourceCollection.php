<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\JsonApi;

use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonSerializable;
use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\Traits\JsonApiTestUtilities;

/**
 * @template T of Model
 */
class JsonApiResourceCollection implements JsonSerializable, Arrayable
{
    use JsonApiTestUtilities;

    protected bool $withIncluded = false;

    protected Collection $models;

    protected ?PaginatorContract $paginator = null;

    protected array $links;

    /** @var Collection<int, JsonApiResource<T>> */
    private Collection $jsonApiResources;

    /**
     * @var Collection<int, JsonApiResource>
     */
    protected Collection $included;

    /**
     * @param  Paginator<T>|PaginatorContract<T>|Collection<int, T>  $models
     */
    public function __construct(Collection|Paginator|PaginatorContract $models)
    {
        if ($models instanceof PaginatorContract) {
            $this->models = collect($models->items());
            $this->paginator = $models;
        } else {
            $this->models = $models;
        }
    }

    /**
     * @param  Paginator<T>|PaginatorContract<T>|Collection<int, T>  $models
     * @return JsonApiResourceCollection<T>
     */
    public static function make(Collection|Paginator|PaginatorContract $models): static
    {
        return new static($models);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        if (! $this->isPrepared()) {
            $this->prepare();
        }

        $payload = [
            'data' => $this->jsonApiResources->jsonSerialize(),
        ];

        if ($this->withIncluded) {
            $payload['included'] = $this->included->jsonSerialize();
        }

        if (count($this->links)) {
            $payload['links'] = $this->links;
        }

        return $payload;
    }

    public function withIncluded(): static
    {
        $this->withIncluded = true;

        return $this;
    }

    /**
     * @return Collection<int, T>
     */
    public function getModels(): Collection
    {
        return $this->models;
    }

    public function isPrepared(): bool
    {
        return isset($this->jsonApiResources, $this->links, $this->included);
    }

    public function prepare(): void
    {
        /** @var Collection<int, JsonApiResource<T>> $resources */
        $resources = $this->models->map(fn ($resource) => JsonApiResource::make($resource)->withoutWrapping());

        $this->included = $this->loadIncluded($resources);
        $this->links = isset($this->paginator) ? $this->linksFromPaginator($this->paginator) : [];
        $this->jsonApiResources = $resources;
    }

    /**
     * @param  Collection<int, JsonApiResource<T>>  $resources
     * @return Collection<int, JsonApiResource>
     */
    protected function loadIncluded(Collection $resources): Collection
    {
        $included = collect();

        foreach ($resources as $resource) {
            foreach ($this->includedFromResource($resource) as $identifier => $includedItem) {
                $included->put($identifier, $includedItem->withoutWrapping());
            }
        }

        return $included->values();
    }

    /**
     * @return Collection<string, JsonApiResource>
     */
    protected function includedFromResource(JsonApiResource $resource): Collection
    {
        return $resource->loadIncluded()->keyBy(fn (JsonApiResource $r) => $r->identifier());
    }

    protected function linksFromPaginator(Paginator|PaginatorContract $paginator): array
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
