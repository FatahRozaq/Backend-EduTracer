<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratIzin extends Model
{
    use HasFactory;

    protected $table = 'surat_izin';

    protected $primaryKey = 'id_surat';

    protected $fillable = [
        'id_user',
        'id_penerima',
        'id_kelas',
        'tanggal',
        'jenis_surat',
        'deskripsi',
        'berkas_surat',
    ];
}
