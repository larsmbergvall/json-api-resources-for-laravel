<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Book;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Review;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\User;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'user_id' => User::factory(),
            'score' => $this->faker->randomFloat(2, 0, 10),
            'created_at' => $this->faker->dateTimeBetween('-5 years'),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }
}
