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
        'id_anak',
        'tanggal',
        'jenis_surat',
        'deskripsi',
        'berkas_surat',
        'read_status',
    ];

    protected $casts = [
        'read_status' => 'boolean',
        'id_kelas' => 'integer',
        'id_user' => 'integer',
        'id_penerima' => 'integer',
        'id_anak' => 'integer',
    ];

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function anak()
    {
        return $this->belongsTo(User::class, 'id_anak');
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'id_penerima');
    }
}
