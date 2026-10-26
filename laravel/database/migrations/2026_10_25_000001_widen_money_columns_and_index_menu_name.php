<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dua penyesuaian untuk kesiapan produksi:
 *
 * 1. Kolom uang dilebarkan ke BIGINT. Sebagai unsignedInteger, PostgreSQL
 *    (Supabase) memakai integer bertanda dengan batas 2.147.483.647 — padahal
 *    validasi mengizinkan 30 baris x qty 99 x harga 10 juta, jadi transaksi
 *    besar gagal insert dengan galat "out of range", bukan pesan validasi.
 *
 * 2. Keunikan nama menu sebelumnya hanya dijaga Rule::unique di MenuRequest.
 *    Dua manajer yang menyimpan nama sama berbarengan tetap lolos; sekarang
 *    dijaga indeks unik di basis data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('total_amount')->default(0)->change();
            $table->unsignedBigInteger('pay_amount')->default(0)->change();
            $table->unsignedBigInteger('change_amount')->default(0)->change();
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->change();
            $table->unsignedBigInteger('subtotal')->change();
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->change();
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->unsignedInteger('price')->change();
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->unsignedInteger('price')->change();
            $table->unsignedInteger('subtotal')->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedInteger('total_amount')->default(0)->change();
            $table->unsignedInteger('pay_amount')->default(0)->change();
            $table->unsignedInteger('change_amount')->default(0)->change();
        });
    }
};
