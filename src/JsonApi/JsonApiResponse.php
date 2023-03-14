<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\JsonApi;

use Illuminate\Http\Response;

class JsonApiResponse extends Response
{
    public function __construct($content = '', $status = 200, array $headers = [])
    {
        parent::__construct($content, $status, $headers);

        $this->headers->set('Content-Type', 'application/vnd.api+json');
    }
}
