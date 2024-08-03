<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwal';
    protected $primaryKey = 'id_jadwal';

    protected $fillable = [
        'id_kelas',
        'id_mata_pelajaran',
        'hari',
        'jam_mulai',
        'jam_akhir',
    ];

    protected $casts = [
        'id_jadwal' => 'integer',
        'id_kelas' => 'integer',
        'id_mata_pelajaran' => 'integer',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mata_pelajaran');
    }
}
