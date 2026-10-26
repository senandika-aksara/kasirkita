<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_id')->nullable()->constrained()->nullOnDelete();
            // Snapshot nama & harga saat transaksi dicatat.
            $table->string('menu_name', 100);
            $table->unsignedInteger('price');
            $table->unsignedSmallInteger('qty');
            $table->unsignedInteger('subtotal');
            $table->timestamps();

            $table->index('menu_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
