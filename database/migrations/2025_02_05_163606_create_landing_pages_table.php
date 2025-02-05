<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Judul dari landing page
            $table->text('content'); // Konten utama (deskripsi, informasi, dll.)
            $table->string('image_url')->nullable(); // URL gambar hero atau lainnya
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('landing_pages');
    }
};
