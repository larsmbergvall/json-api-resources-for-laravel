<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Author;

/** @extends Factory<Author> */
class AuthorFactory extends Factory
{
    protected $model = Author::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'country_of_origin' => fake()->country(),
            'bio' => fake()->paragraphs(asText: true),
            'born_at' => fake()->dateTimeBetween('-80 years'),
        ];
    }
}
