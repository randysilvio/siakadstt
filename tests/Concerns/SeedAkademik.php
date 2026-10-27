<?php

namespace Tests\Concerns;

use App\Models\Dosen;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\Role;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Data akademik minimum untuk pengujian alur SIAKAD: dua prodi, satu tahun akademik aktif,
 * satu dosen (wali & pengampu), satu mahasiswa aktif, satu admin, dan mata kuliah per prodi.
 */
trait SeedAkademik
{
    public function role(string $name): Role
    {
        return Role::firstOrCreate(['name' => $name], ['display_name' => ucfirst($name)]);
    }

    public function buatUser(string $nama, string $email, array $roles): User
    {
        $user = User::create(['name' => $nama, 'email' => $email, 'password' => Hash::make('password123')]);
        foreach ($roles as $r) {
            $user->roles()->attach($this->role($r));
        }
        return $user;
    }

    public function seedAkademik(): array
    {
        $prodi = ProgramStudi::create(['nama_prodi' => 'Teologi', 'kode_prodi' => 'TH']);
        $prodiLain = ProgramStudi::create(['nama_prodi' => 'Pendidikan Agama Kristen', 'kode_prodi' => 'PK']);

        $ta = TahunAkademik::create([
            'tahun' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true,
            'tanggal_mulai_krs' => now()->subDay()->toDateString(),
            'tanggal_selesai_krs' => now()->addMonth()->toDateString(),
        ]);

        $kur = Kurikulum::create(['nama_kurikulum' => 'K-TH', 'tahun' => 2024, 'is_active' => true, 'program_studi_id' => $prodi->id]);
        $kurLain = Kurikulum::create(['nama_kurikulum' => 'K-PK', 'tahun' => 2024, 'is_active' => true, 'program_studi_id' => $prodiLain->id]);

        $dUser = $this->buatUser('Dosen A', 'dosen@x.test', ['dosen']);
        $dosen = Dosen::create(['user_id' => $dUser->id, 'nidn' => '111', 'nama_lengkap' => 'Dosen A', 'jenis_pengajar' => 'Dosen', 'program_studi_id' => $prodi->id]);

        $mk = MataKuliah::create(['kurikulum_id' => $kur->id, 'kode_mk' => 'TH101', 'nama_mk' => 'Dogmatika', 'sks' => 3, 'semester' => 1, 'dosen_id' => $dosen->id]);
        $mkLain = MataKuliah::create(['kurikulum_id' => $kurLain->id, 'kode_mk' => 'PK101', 'nama_mk' => 'Didaktik', 'sks' => 3, 'semester' => 1, 'dosen_id' => $dosen->id]);
        $mk->jadwals()->create(['nama_kelas' => 'A', 'hari' => 'Senin', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00', 'kuota' => 40]);
        $mk->jadwals()->create(['nama_kelas' => 'B', 'hari' => 'Rabu', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00', 'kuota' => 40]);
        $mkLain->jadwals()->create(['nama_kelas' => 'A', 'hari' => 'Selasa', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00', 'kuota' => 40]);

        $mUser = $this->buatUser('Mhs A', 'mhs@x.test', ['mahasiswa']);
        $mhs = Mahasiswa::create([
            'user_id' => $mUser->id, 'nim' => '2026TH001', 'nama_lengkap' => 'Mhs A', 'program_studi_id' => $prodi->id,
            'status_mahasiswa' => 'Aktif', 'status_krs' => 'Belum Mengajukan', 'tahun_masuk' => 2026,
            'jenis_kelamin' => 'L', 'nama_ibu_kandung' => 'Ibu', 'dosen_wali_id' => $dosen->id,
        ]);

        $admin = $this->buatUser('Admin', 'admin@x.test', ['admin']);

        return compact('prodi', 'prodiLain', 'ta', 'mk', 'mkLain', 'mhs', 'mUser', 'dosen', 'dUser', 'admin');
    }

    /** Mahasiswa mengambil MK pada semester aktif dan KRS-nya disetujui. */
    public function ambilDanSetujui(array $d, ?MataKuliah $mk = null, string $kelas = 'A'): void
    {
        $mk ??= $d['mk'];
        $d['mhs']->mataKuliahs()->attach($mk->id, ['tahun_akademik_id' => $d['ta']->id, 'nama_kelas' => $kelas]);
        $d['mhs']->setStatusKrs($d['ta']->id, 'Disetujui', null, $d['dUser']->id);
    }
}
