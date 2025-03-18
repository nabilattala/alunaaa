<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // Produk yang mendapatkan diskon
            $table->string('code')->nullable()->unique(); // Kode opsional dan unik jika diisi
            $table->integer('percentage'); // Diskon dalam persen
            $table->date('expires_at'); // Tanggal kedaluwarsa
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Admin yang membuat
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
