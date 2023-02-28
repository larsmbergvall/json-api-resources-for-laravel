<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Larsmbergvall\JsonApiResourcesForLaravel\Attributes\JsonApiIncludeAttributes;
use Larsmbergvall\JsonApiResourcesForLaravel\Attributes\JsonApiIncludeRelationships;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\database\factories\AuthorFactory;

#[JsonApiIncludeAttributes(['id', 'name', 'country_of_origin', 'bio', 'created_at'])]
#[JsonApiIncludeRelationships(['books'])]
class Author extends Model
{
    use HasFactory;

    protected $table = 'authors';

    protected $guarded = ['id'];

    protected $casts = [
        'born_at' => 'datetime',
    ];

    protected static function newFactory(): AuthorFactory
    {
        return new AuthorFactory();
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
