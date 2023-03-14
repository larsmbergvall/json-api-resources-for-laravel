<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models;

class AuthorWithHiddenNameAttribute extends Author
{
    use CreateFromBaseFactory;

    protected $hidden = ['name'];
}
