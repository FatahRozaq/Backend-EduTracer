<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReadStatusToSuratIzinTable extends Migration
{
    public function up()
    {
        Schema::table('surat_izin', function (Blueprint $table) {
            $table->boolean('read_status')->default(false)->after('berkas_surat');
        });
    }

    public function down()
    {
        Schema::table('surat_izin', function (Blueprint $table) {
            $table->dropColumn('read_status');
        });
    }
}
