<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\User;

/** @extends Factory<User> */
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
