<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kelas extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'kelas';
    protected $primaryKey = 'id_kelas';

    protected $fillable = [
        'nama_kelas',
        'deskripsi',
        'enrollment_key',
        'wakel_id'
    ];

    protected $casts = [
        'id_kelas' => 'integer',
        'wakel_id' => 'integer'
    ];

    public function mataPelajaran()
    {
        return $this->belongsToMany(MataPelajaran::class, 'kelas_mata_pelajaran', 'id_kelas', 'id_mata_pelajaran')
            // ->using(KelasMataPelajaran::class)
            ->withTimestamps()
            ->withPivot('id_kelas_mata_pelajaran');
    }

    public function user()
    {
        return $this->belongsToMany(User::class, 'kelas_user', 'id_kelas', 'id_user');
    }
   
    public function kelasUsers()
    {
        return $this->hasMany(KelasUser::class, 'id_kelas');
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, KelasUser::class, 'id_kelas', 'id_user', 'id_kelas', 'id_user');
    }

    public function kelasMataPelajaran()
    {
        return $this->hasMany(KelasMataPelajaran::class, 'id_kelas');
    }

    public function wakel()
    {
        return $this->belongsTo(User::class, 'wakel_id');
    }

    public function rapots()
    {
        return $this->hasMany(Rapot::class, 'id_kelas');
    }
}
