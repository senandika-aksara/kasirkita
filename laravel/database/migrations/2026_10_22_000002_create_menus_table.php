<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name', 100);
            // Harga disimpan sebagai rupiah bulat (integer), bukan desimal.
            $table->unsignedInteger('price');
            $table->string('image')->nullable();
            $table->enum('status', ['tersedia', 'habis'])->default('tersedia');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
