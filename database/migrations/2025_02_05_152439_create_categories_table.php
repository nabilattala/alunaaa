<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Category;

return new class extends Migration {
    public function up(): void {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Tambahkan kategori default "Uncategorized"
        Category::create(['name' => 'Uncategorized']);
    }

    public function down(): void {
        Schema::dropIfExists('categories');
    }
};
