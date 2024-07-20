<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPengajar extends Model
{
    use HasFactory;

    protected $table = 'jadwal_penagajar';
    protected $primaryKey = 'id_jadwal_pengajar';

    protected $fillable = [
        'id_jadwal',
        'id_user',
    ];

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'id_jadwal');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
