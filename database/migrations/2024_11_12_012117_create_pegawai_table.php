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
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nip')->unique();
            $table->string('nama');
            $table->string('tempat_lahir');
            $table->string('alamat');
            $table->date('tanggal_lahir');
            $table->string('jenis_kelamin');
            $table->string('golongan');
            $table->string('eselon');
            $table->string('jabatan');
            $table->string('agama');
            $table->string('no_hp');
            $table->string('npwp')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('unit_kerja_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('unit_kerja_id')
              ->references('id')->on('unit_kerja')
              ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
