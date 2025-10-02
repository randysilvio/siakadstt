<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\DB;

class MahasiswasImport implements ToModel, WithHeadingRow, WithValidation
{
    private $mahasiswaRole;

    public function __construct()
    {
        $this->mahasiswaRole = Role::where('name', 'mahasiswa')->first();
    }

    public function model(array $row)
    {
        // --- PERBAIKAN LOGIKA PENYIMPANAN ---

        // Inisialisasi variabel untuk menampung model yang berhasil dibuat
        $mahasiswa = null; 
        
        DB::transaction(function () use ($row, &$mahasiswa) {
            // 1. Cari atau buat user baru
            $user = User::firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['nama_lengkap'],
                    'password' => Hash::make($row['password']),
                ]
            );

            // 2. Lampirkan peran 'mahasiswa' ke pengguna
            if ($this->mahasiswaRole) {
                $user->roles()->syncWithoutDetaching($this->mahasiswaRole->id);
            }

            // 3. Buat dan SIMPAN data mahasiswa secara eksplisit di dalam transaksi
            // Hasilnya disimpan ke dalam variabel $mahasiswa
            $mahasiswa = Mahasiswa::create([
                'user_id'           => $user->id,
                'nim'               => $row['nim'],
                'nama_lengkap'      => $row['nama_lengkap'],
                'program_studi_id'  => $row['program_studi_id'],
                'dosen_wali_id'     => $row['dosen_wali_id'],
                'tahun_masuk'       => $row['tahun_masuk'],
                'tempat_lahir'      => $row['tempat_lahir'],
                'tanggal_lahir'     => !empty($row['tanggal_lahir']) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tanggal_lahir'])->format('Y-m-d') : null,
                'jenis_kelamin'     => $row['jenis_kelamin'],
                'alamat'            => $row['alamat'],
                'nomor_telepon'     => $row['nomor_telepon'],
                'status_mahasiswa'  => 'Aktif',
            ]);
        });
        
        // 4. Kembalikan model yang sudah tersimpan
        return $mahasiswa;
    }

    public function rules(): array
    {
        return [
            'nim'               => 'required|string|unique:mahasiswas,nim',
            'nama_lengkap'      => 'required|string|max:255',
            'program_studi_id'  => 'required|integer|exists:program_studis,id',
            'email'             => 'required|email|unique:users,email', 
            'password'          => 'required|string|min:8',
            'dosen_wali_id'     => 'nullable|integer|exists:dosens,id',
            'tahun_masuk'       => 'required|digits:4|integer',
            'tempat_lahir'      => 'nullable|string',
            'tanggal_lahir'     => 'nullable|numeric',
            'jenis_kelamin'     => 'nullable|in:L,P',
            'alamat'            => 'nullable|string',
            'nomor_telepon'     => 'nullable|string',
        ];
    }
}