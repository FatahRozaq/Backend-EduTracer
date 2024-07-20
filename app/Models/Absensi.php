<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';
    protected $primaryKey = 'id_absen';

    protected $fillable = [
        'id_kelas_mata_pelajaran', 'id_jadwal', 'id_user', 'status_kehadiran', 'tanggal'
    ];

    
    public function kelasMataPelajaran()
    {
        return $this->belongsTo(KelasMataPelajaran::class, 'id_kelas_mata_pelajaran');
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'id_jadwal');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
