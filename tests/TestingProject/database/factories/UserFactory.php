<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->email(),
        ];
    }
}
