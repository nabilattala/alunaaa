<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('url')->nullable();
            $table->string('video_url')->nullable();
<<<<<<< HEAD
            // $table->json('images')->nullable();
            $table->string('images_path')->nullable();
            $table->string('images_url')->nullable();
=======
            $table->string('image')->nullable();
>>>>>>> d1d3ebd3c1795f4b847c9b61b7441c20a5eefbe5
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('price')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
