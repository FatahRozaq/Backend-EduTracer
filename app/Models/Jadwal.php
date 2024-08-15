<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth; 

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
        'jam_akhir'
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

    public function pengajarMapel()
    {
        return $this->hasMany(PengajarMapel::class, 'id_mata_pelajaran', 'id_mata_pelajaran')
            ->where('id_user', Auth::id());
    }

    public function jadwalPengajar()
    {
        return $this->hasMany(JadwalPengajar::class, 'id_jadwal');
    }
}
