<?php

use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResource;
use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResourceCollection;
use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResponse;

if (! function_exists('jsonApiResponse')) {
    function jsonApiResponse(JsonApiResource|JsonApiResourceCollection $content, int $status = 200, array $headers = []): JsonApiResponse
    {
        return new JsonApiResponse($content->jsonSerialize(), $status, $headers);
    }
}
