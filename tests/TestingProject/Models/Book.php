<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    public function author(): BelongsTo
    {
        return $this->belongsTo(AuthorWithIncludeAttributesAttribute::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
