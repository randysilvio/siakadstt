<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows; // Wajib: Anti Baris Kosong

use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class MahasiswasImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    private $mahasiswaRole;

    public function __construct()
    {
        $this->mahasiswaRole = Role::where('name', 'mahasiswa')->first();
    }

    public function model(array $row)
    {
        // 1. Cek baris kosong (Anti error "Field required")
        if (empty($row['nim']) || empty($row['nama_lengkap'])) {
            return null;
        }

        $nim = trim((string) $row['nim']);
        $email = trim($row['email']);
        
        // 2. [FIX UTAMA] Sanitasi Dosen Wali ID
        // Jika di Excel tertulis '0', kosong, atau strip, ubah jadi NULL agar DB tidak error
        $dosenWaliId = $row['dosen_wali_id'];
        if (empty($dosenWaliId) || $dosenWaliId == '0' || $dosenWaliId == '-') {
            $dosenWaliId = null;
        }

        // Handle Tanggal Lahir
        $tanggalLahir = null;
        if (!empty($row['tanggal_lahir'])) {
            try {
                if (is_numeric($row['tanggal_lahir'])) {
                    $tanggalLahir = Date::excelToDateTimeObject($row['tanggal_lahir'])->format('Y-m-d');
                } else {
                    $tanggalLahir = Carbon::parse($row['tanggal_lahir'])->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $tanggalLahir = null;
            }
        }

        return DB::transaction(function () use ($row, $nim, $email, $tanggalLahir, $dosenWaliId) {
            // A. UPDATE / CREATE USER
            $user = User::where('email', $email)->first();
            
            if ($user) {
                $userData = ['name' => $row['nama_lengkap']];
                if (!empty($row['password'])) {
                    $userData['password'] = Hash::make($row['password']);
                }
                $user->update($userData);
            } else {
                $user = User::create([
                    'name' => $row['nama_lengkap'],
                    'email' => $email,
                    'password' => Hash::make($row['password']),
                ]);
            }

            if ($this->mahasiswaRole) {
                $user->roles()->syncWithoutDetaching($this->mahasiswaRole->id);
            }

            // B. UPDATE / CREATE MAHASISWA
            return Mahasiswa::updateOrCreate(
                ['nim' => $nim], 
                [
                    'user_id'           => $user->id,
                    'nama_lengkap'      => $row['nama_lengkap'],
                    'program_studi_id'  => $row['program_studi_id'],
                    'dosen_wali_id'     => $dosenWaliId, // Gunakan variabel yang sudah dibersihkan (bisa null)
                    'tahun_masuk'       => $row['tahun_masuk'],
                    'tempat_lahir'      => $row['tempat_lahir'] ?? null,
                    'tanggal_lahir'     => $tanggalLahir,
                    'jenis_kelamin'     => $row['jenis_kelamin'] ?? null,
                    'alamat'            => $row['alamat'] ?? null,
                    'nomor_telepon'     => $row['nomor_telepon'] ?? null,
                    'status_mahasiswa'  => $row['status_mahasiswa'] ?? 'Aktif',
                ]
            );
        });
    }

    public function rules(): array
    {
        return [
            'nim'               => 'required', 
            'nama_lengkap'      => 'required',
            // [PENTING] Validasi 'exists' mencegah error SQL Crash.
            // ID Prodi harus benar-benar ada di tabel program_studis
            'program_studi_id'  => 'required|exists:program_studis,id', 
            // Dosen Wali boleh kosong, tapi jika diisi angkanya harus ada di tabel dosens
            'dosen_wali_id'     => 'nullable', 
            'email'             => 'required|email',
        ];
    }
}