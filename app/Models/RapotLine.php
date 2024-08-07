<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RapotLine extends Model
{
    use HasFactory;

    protected $table = 'rapot_line';

    protected $fillable = [
        'id_rapot',
        'id_mapel',
        'nilai',
        'notes',
    ];

    protected $casts = [
        'id' => 'integer',
        'id_rapot' => 'integer',
        'id_mapel' => 'integer',
    ];

    public function rapot()
    {
        return $this->belongsTo(Rapot::class, 'id_rapot');
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mapel', 'id_mata_pelajaran');
    }

    public function rapotLines()
    {
        return $this->hasMany(RapotLine::class, 'id_mapel', 'id_mata_pelajaran');
    }
}
