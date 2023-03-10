<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\AuthorWithIncludeAttributesAttribute;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Book;

/** @extends Factory<Book> */
class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'year' => $this->faker->numberBetween(1800, now()->year),
            'isbn' => $this->faker->isbn10(),
            'author_id' => AuthorWithIncludeAttributesAttribute::factory(),
        ];
    }
}
