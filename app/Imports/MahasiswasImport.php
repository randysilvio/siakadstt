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
use Maatwebsite\Excel\Concerns\SkipsEmptyRows; // [WAJIB] Obat anti error baris kosong

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
        // [PENTING] Validasi Manual: Jika NIM kosong, ABAIKAN baris ini.
        // Ini akan mengebalkan sistem dari kolom referensi atau baris hantu di Excel.
        if (empty($row['nim']) || empty($row['nama_lengkap'])) {
            return null;
        }

        $nim = trim((string) $row['nim']);
        $email = trim($row['email']);
        
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

        return DB::transaction(function () use ($row, $nim, $email, $tanggalLahir) {
            // 1. UPDATE USER (Jika ada) / CREATE USER (Jika baru)
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

            // 2. UPDATE / CREATE MAHASISWA
            return Mahasiswa::updateOrCreate(
                ['nim' => $nim], // Kunci Unik (NIM)
                [
                    'user_id'           => $user->id,
                    'nama_lengkap'      => $row['nama_lengkap'],
                    'program_studi_id'  => $row['program_studi_id'],
                    'dosen_wali_id'     => $row['dosen_wali_id'] ?? null,
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
            // Validasi exists agar tidak crash jika ID Prodi salah
            'program_studi_id'  => 'required|exists:program_studis,id', 
            'email'             => 'required|email',
        ];
    }
}