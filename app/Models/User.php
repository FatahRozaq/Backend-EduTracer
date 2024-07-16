<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'email',
        'password',
        'no_tlp',
        'alamat',
        'roles',
        'partner_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // public function kelas()
    // {
    //     return $this->belongsToMany(Kelas::class);
    // }

    public function parent()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    // Relasi ke departemen anak (child)
    public function children()
    {
        return $this->hasMany(User::class, 'partner_id');
    }

    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'kelas_user', 'id_user', 'id_kelas');
    }

    public function mataPelajaran()
    {
        $kelas = $this->kelas()->first();

        if ($kelas) {
            return $kelas->mataPelajaran()->get();
        }

        return collect();
    }

    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_child', 'child_id', 'parent_id')->withPivot('status');
    }

    public function childrenMany()
    {
        return $this->belongsToMany(User::class, 'parent_child', 'parent_id', 'child_id')->withPivot('status');
    }
}
