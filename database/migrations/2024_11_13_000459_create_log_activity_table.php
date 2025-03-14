<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('log_activity', function (Blueprint $table) {
            $table->id();  // ID unik untuk setiap log
            $table->unsignedBigInteger('user_id');  // Menyimpan ID user yang beraktivitas
            $table->string('activity_type');  // Jenis aktivitas (misalnya login, update)
            $table->timestamps();  // Tanggal dan waktu aktivitas dicatat

            // Menetapkan relasi dengan tabel `users`
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_activity');
    }
};
