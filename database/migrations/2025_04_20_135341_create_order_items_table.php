<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id(); // ID unik untuk setiap item pesanan
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // Relasi ke tabel orders
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Relasi ke tabel products
            $table->integer('quantity'); // Jumlah produk yang dipesan
            $table->decimal('price', 10, 2); // Harga per unit produk
            $table->timestamps(); // Waktu pembuatan dan pembaruan
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
}
