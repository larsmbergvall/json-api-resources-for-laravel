<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\JsonApi;

use Illuminate\Http\Response;

class JsonApiResponse extends Response
{
    public const JSON_API_CONTENT_TYPE = 'application/vnd.api+json';

    public function __construct($content = '', $status = 200, array $headers = [])
    {
        parent::__construct($content, $status, $headers);

        $this->headers->set('Content-Type', self::JSON_API_CONTENT_TYPE);
    }
}
