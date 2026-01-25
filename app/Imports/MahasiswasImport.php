<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use App\Models\User;
use App\Models\Role;
use App\Models\ProgramStudi;
use App\Models\Dosen;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class MahasiswasImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public function headingRow(): int
    {
        return 2; // Baris 1: Judul Template, Baris 2: Header Kolom
    }

    public function model(array $row)
    {
        // 1. Validasi Data Utama
        if (empty($row['nim']) || empty($row['nama_lengkap'])) {
            return null;
        }

        $nim = trim((string) $row['nim']);

        // 2. Cari Role Mahasiswa
        $role = Role::where('name', 'LIKE', 'mahasiswa')
                    ->orWhere('name', 'LIKE', 'student')
                    ->first();
        $roleId = $role ? $role->id : 3; 

        // 3. Setup Prodi (PERBAIKAN KUNCI: 'program_studi')
        $prodiId = null;
        // Cek 'program_studi' (sesuai CSV) ATAU 'program_studi_id' (jaga-jaga)
        $inputProdi = $row['program_studi'] ?? $row['program_studi_id'] ?? null;

        if (!empty($inputProdi)) {
            if (is_numeric($inputProdi)) {
                $prodiId = $inputProdi;
            } else {
                $prodi = ProgramStudi::where('nama_prodi', 'LIKE', '%' . $inputProdi . '%')->first();
                $prodiId = $prodi ? $prodi->id : null;
            }
        }

        // Default Prodi jika Kosong (Agar tidak error SQL Integrity)
        // PERINGATAN: Pastikan ID 1 ada di database (misal: Teologi)
        if (empty($prodiId)) {
            $prodiId = 1; 
        }

        // 4. Setup Dosen Wali (PERBAIKAN KUNCI: 'dosen_wali')
        $dosenWaliId = null;
        $inputDosen = $row['dosen_wali'] ?? $row['dosen_wali_id'] ?? null;

        if (!empty($inputDosen)) {
            if (is_numeric($inputDosen)) {
                $dosenWaliId = $inputDosen;
            } else {
                $dosen = Dosen::where('nama_lengkap', 'LIKE', '%' . $inputDosen . '%')->first();
                $dosenWaliId = $dosen ? $dosen->id : null;
            }
        }

        // 5. Parsing Tanggal Lahir
        $tanggalLahir = null;
        if (!empty($row['tanggal_lahir'])) {
            try {
                if (is_numeric($row['tanggal_lahir'])) {
                    $tanggalLahir = Date::excelToDateTimeObject($row['tanggal_lahir'])->format('Y-m-d');
                } else {
                    $tanggalLahir = Carbon::parse($row['tanggal_lahir'])->format('Y-m-d');
                }
            } catch (\Exception $e) { $tanggalLahir = null; }
        }

        // 6. MANAJEMEN USER (Update or Create)
        $email = !empty($row['email']) ? trim($row['email']) : $nim . '@student.sttgpipapua.ac.id';
        $password = !empty($row['password']) ? Hash::make($row['password']) : Hash::make($nim);

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $row['nama_lengkap'],
                'password' => $password,
                'role_id' => $roleId
            ]
        );

        if ($role && method_exists($user, 'assignRole')) {
            $user->assignRole($role->name);
        }

        // 7. Simpan Data Mahasiswa
        return Mahasiswa::updateOrCreate(
            ['nim' => $nim],
            [
                'user_id'           => $user->id,
                'nama_lengkap'      => $row['nama_lengkap'],
                'program_studi_id'  => $prodiId, // Pastikan tidak null
                'dosen_wali_id'     => $dosenWaliId,
                
                // PERBAIKAN KUNCI: 'angkatan' (sesuai CSV)
                'angkatan'          => $row['angkatan'] ?? $row['tahun_masuk'] ?? date('Y'),
                
                'status_mahasiswa'  => $row['status_mahasiswa'] ?? 'Aktif',
                'nik'               => $row['nik'] ?? null,
                'nisn'              => $row['nisn'] ?? null,
                'jalur_pendaftaran' => $row['jalur_pendaftaran'] ?? 'Mandiri',
                'tempat_lahir'      => $row['tempat_lahir'] ?? null,
                'tanggal_lahir'     => $tanggalLahir,
                'jenis_kelamin'     => $row['jenis_kelamin'] ?? 'L',
                'agama'             => $row['agama'] ?? 'Kristen Protestan',
                
                // PERBAIKAN KUNCI: 'no_hp' (sesuai CSV)
                'nomor_telepon'     => $row['no_hp'] ?? $row['nomor_telepon'] ?? null,
                
                'alamat'            => $row['alamat'] ?? null,
                'nama_ibu_kandung'  => $row['nama_ibu_kandung'] ?? null,
                'nama_ayah'         => $row['nama_ayah'] ?? null,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'nim' => 'required',
            'nama_lengkap' => 'required',
        ];
    }
}