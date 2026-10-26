<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->string('table_number', 10)->nullable();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            // Semua nilai uang adalah rupiah bulat (integer).
            $table->unsignedInteger('total_amount')->default(0);
            $table->unsignedInteger('pay_amount')->default(0);
            $table->unsignedInteger('change_amount')->default(0);
            $table->enum('payment_status', ['lunas', 'belum_bayar'])->default('belum_bayar');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('payment_status');
            $table->index('created_at');
            $table->index('paid_at');
            $table->index(['payment_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
