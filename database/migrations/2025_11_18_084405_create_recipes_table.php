<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->string('image');
            $table->integer('ready_in_minutes')->default(30);
            $table->integer('servings')->default(2);
            $table->integer('health_score')->default(50);
            $table->decimal('price_per_serving', 8, 2)->default(0);
            $table->text('instructions')->nullable();
            $table->json('categories')->nullable();
            $table->boolean('vegetarian')->default(false);
            $table->boolean('vegan')->default(false);
            $table->boolean('gluten_free')->default(false);
            $table->boolean('dairy_free')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
