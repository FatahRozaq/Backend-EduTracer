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
        'id_kelas', 'id_mata_pelajaran', 'tanggal', 'hari', 'jam_mulai', 'jam_akhir'
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mata_pelajaran');
    }

    public function getHariAttribute($value)
    {
        return explode(',', $value);
    }

    public function setHariAttribute($value)
    {
        $this->attributes['hari'] = implode(',', $value);
    }

}
