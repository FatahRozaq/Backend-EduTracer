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
        Schema::create('rapot_line', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_rapot');
            $table->unsignedBigInteger('id_mapel');
            $table->double('nilai', 8, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('id_rapot')->references('id')->on('rapot')->onDelete('cascade');
            $table->foreign('id_mapel')->references('id_mata_pelajaran')->on('mata_pelajaran')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapot_line');
    }
};
