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
        return 2;
    }

    public function model(array $row)
    {
        if (empty($row['nim']) || empty($row['nama_lengkap'])) {
            return null;
        }

        $nim = trim((string) $row['nim']);

        // 1. Skip jika NIM sudah ada di data mahasiswa
        if (Mahasiswa::where('nim', $nim)->exists()) {
            return null;
        }

        // 2. Cari Role Mahasiswa (Case Insensitive agar aman)
        // Mencari 'mahasiswa', 'Mahasiswa', atau 'MAHASISWA'
        $role = Role::where('name', 'LIKE', 'mahasiswa')->first();
        $roleId = $role ? $role->id : 3; // Default ke ID 3 jika tidak ketemu

        // 3. Setup Prodi & Dosen (Logika Cerdas)
        $prodiId = null;
        if (!empty($row['program_studi_id'])) { // Sesuai CSV baru
            if (is_numeric($row['program_studi_id'])) {
                $prodiId = $row['program_studi_id'];
            } else {
                $prodi = ProgramStudi::where('nama_prodi', 'LIKE', '%' . $row['program_studi_id'] . '%')->first();
                $prodiId = $prodi ? $prodi->id : null;
            }
        }

        $dosenWaliId = null;
        if (!empty($row['dosen_wali_id'])) {
             // Jika input angka, pakai ID. Jika text, cari nama.
            if (is_numeric($row['dosen_wali_id'])) {
                $dosenWaliId = $row['dosen_wali_id'];
            } else {
                $dosen = Dosen::where('nama_lengkap', 'LIKE', '%' . $row['dosen_wali_id'] . '%')->first();
                $dosenWaliId = $dosen ? $dosen->id : null;
            }
        }

        // 4. Parsing Tanggal
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

        // 5. Buat atau Update User (PAKSA ROLE UPDATE)
        $email = !empty($row['email']) ? trim($row['email']) : $nim . '@student.sttgpipapua.ac.id';
        
        $user = User::updateOrCreate(
            ['email' => $email], // Kunci pencarian
            [
                'name' => $row['nama_lengkap'],
                'password' => !empty($row['password']) ? Hash::make($row['password']) : Hash::make($nim),
                'role_id' => $roleId // Pastikan kolom ini ter-update
            ]
        );

        // Jika menggunakan Spatie Permission (Opsional, jaga-jaga)
        if (method_exists($user, 'assignRole') && $role) {
            $user->assignRole($role->name);
        }

        // 6. Simpan Data Mahasiswa
        return new Mahasiswa([
            'nim'               => $nim,
            'user_id'           => $user->id,
            'nama_lengkap'      => $row['nama_lengkap'],
            'program_studi_id'  => $prodiId,
            'dosen_wali_id'     => $dosenWaliId,
            'angkatan'          => $row['tahun_masuk'] ?? date('Y'), // CSV pakai 'tahun_masuk'
            'status_mahasiswa'  => $row['status_mahasiswa'] ?? 'Aktif',
            'nik'               => $row['nik'] ?? null,
            'nisn'              => $row['nisn'] ?? null,
            'jalur_pendaftaran' => $row['jalur_pendaftaran'] ?? 'Mandiri',
            'tempat_lahir'      => $row['tempat_lahir'] ?? null,
            'tanggal_lahir'     => $tanggalLahir,
            'jenis_kelamin'     => $row['jenis_kelamin'] ?? 'L',
            'agama'             => $row['agama'] ?? 'Kristen Protestan',
            'nomor_telepon'     => $row['nomor_telepon'] ?? null, // CSV pakai 'nomor_telepon'
            'alamat'            => $row['alamat'] ?? null,
            // Field detail lainnya sesuai CSV
            'nama_ibu_kandung'  => $row['nama_ibu_kandung'] ?? null,
            'nama_ayah'         => $row['nama_ayah'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'nim' => 'required',
            'nama_lengkap' => 'required',
        ];
    }
}