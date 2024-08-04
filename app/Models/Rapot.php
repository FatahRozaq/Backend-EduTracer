<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rapot extends Model
{
    use HasFactory;

    protected $table = 'rapot';

    protected $fillable = [
        'jenis',
        'id_siswa',
        'id_kelas',
        'semester',
    ];
    protected $casts = [
        'id_siswa' => 'integer',
        'id_kelas' => 'integer',
        'id' => 'integer',
    ];

    public function siswa()
    {
        return $this->belongsTo(User::class, 'id_siswa');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function rapotLines()
    {
        return $this->hasMany(RapotLine::class, 'id_rapot');
    }
}
