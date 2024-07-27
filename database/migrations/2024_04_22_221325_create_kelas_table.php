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
        Schema::create('kelas', function (Blueprint $table) {
            $table->id('id_kelas');
            $table->string('nama_kelas');
            $table->string('deskripsi')->nullable();
            $table->string('enrollment_key')->unique();
            $table->unsignedBigInteger('wakel_id')->nullable(); // Add this line
            $table->timestamps();

            $table->foreign('wakel_id')->references('id')->on('users')->onDelete('set null'); // Add this line
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
