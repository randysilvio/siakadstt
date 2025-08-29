<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $judul_kegiatan
 * @property string|null $deskripsi
 * @property Carbon $tanggal_mulai
 * @property Carbon $tanggal_selesai
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[] $roles
 */
class KegiatanAkademik extends Model
{
    use HasFactory;

    /**
     * Menentukan nama tabel yang digunakan oleh model.
     * @var string
     */
    protected $table = 'kegiatan_akademik';

    /**
     * The attributes that are mass assignable.
     * Hapus 'target_role' karena sudah tidak digunakan.
     * @var array<int, string>
     */
    protected $fillable = [
        'judul_kegiatan',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Relasi Many-to-Many ke model Role.
     * Satu kegiatan bisa memiliki banyak peran target.
     */
    public function roles(): BelongsToMany
    {
        // Asumsi nama tabel pivot adalah 'kegiatan_akademik_role'
        // dengan foreign key 'kegiatan_akademik_id' dan 'role_id'
        return $this->belongsToMany(Role::class, 'kegiatan_akademik_role');
    }
}