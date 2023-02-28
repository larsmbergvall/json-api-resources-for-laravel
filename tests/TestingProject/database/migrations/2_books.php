<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Larsmbergvall\JsonApiResourcesForLaravel\Tests\TestingProject\Models\Author;

return new class extends Migration
{
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->unsignedSmallInteger('year');
            $table->string('isbn')->unique();
            $table->foreignIdFor(Author::class)->constrained();

            $table->timestamps();
        });
    }
};
