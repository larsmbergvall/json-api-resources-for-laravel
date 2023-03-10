<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Book;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\User;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Book::class)->constrained();
            $table->decimal('score');
            $table->foreignIdFor(User::class)->nullable()->constrained();

            $table->timestamps();
        });
    }
};
