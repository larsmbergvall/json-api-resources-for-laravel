<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiError;
use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiErrorSource;

class JsonApiMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $response->headers->set('Content-Type', 'application/vnd.api+json');

        return $this->transformValidationErrors($response);
    }

    private function transformValidationErrors(JsonResponse|Response $response): JsonResponse|Response
    {
        if ($response->status() !== 422) {
            return $response;
        }

        $errors = json_decode($response->getContent(), true);

        if (! $errors || ! array_key_exists('errors', $errors) || ! count($errors['errors'])) {
            return $response;
        }

        $jsonApiErrors = [];

        foreach ($errors['errors'] as $pointer => $error) {
            $parameter = Str::of($pointer)->explode('.')->last();
            $jsonApiErrors[] = new JsonApiError($response->status(), $response->statusText(), $error[0] ?? '', new JsonApiErrorSource(Str::replace('.', '/', $pointer), $parameter));
        }

        return $response->setContent(json_encode(['errors' => $jsonApiErrors]));
    }
}
