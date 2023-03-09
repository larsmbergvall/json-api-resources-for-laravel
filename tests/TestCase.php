<?php

namespace Larsmbergvall\JsonApiResourcesForLaravel\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Larsmbergvall\JsonApiResourcesForLaravel\JsonApiResourcesForLaravelServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Larsmbergvall\\JsonApiResourcesForLaravel\\Tests\\TestingProject\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            JsonApiResourcesForLaravelServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $migrations = [
            __DIR__.'/TestingProject/database/migrations/0_users.php',
            __DIR__.'/TestingProject/database/migrations/1_authors.php',
            __DIR__.'/TestingProject/database/migrations/2_books.php',
            __DIR__.'/TestingProject/database/migrations/3_reviews.php',
        ];

        foreach ($migrations as $migrationPath) {
            $migration = include $migrationPath;
            $migration->up();
        }
    }
}
