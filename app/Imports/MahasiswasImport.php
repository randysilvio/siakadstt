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
        return 2; // Baris 1: Judul, Baris 2: Header
    }

    public function model(array $row)
    {
        // 1. Validasi Data Utama
        if (empty($row['nim']) || empty($row['nama_lengkap'])) {
            return null;
        }

        $nim = trim((string) $row['nim']);

        // 2. Cari Role Mahasiswa (Case Insensitive & Flexible)
        // Mencari role dengan nama 'mahasiswa' atau 'student'
        $role = Role::where('name', 'LIKE', 'mahasiswa')
                    ->orWhere('name', 'LIKE', 'student')
                    ->first();
        
        // Jika role tidak ketemu di DB, set default ID (sesuaikan ID role mahasiswa di DB Anda, biasanya 3)
        $roleId = $role ? $role->id : 3; 

        // 3. Setup Prodi & Dosen (Logika Cerdas ID vs Nama)
        $prodiId = null;
        if (!empty($row['program_studi_id'])) {
            if (is_numeric($row['program_studi_id'])) {
                $prodiId = $row['program_studi_id'];
            } else {
                $prodi = ProgramStudi::where('nama_prodi', 'LIKE', '%' . $row['program_studi_id'] . '%')->first();
                $prodiId = $prodi ? $prodi->id : null;
            }
        }

        $dosenWaliId = null;
        if (!empty($row['dosen_wali_id'])) {
            if (is_numeric($row['dosen_wali_id'])) {
                $dosenWaliId = $row['dosen_wali_id'];
            } else {
                $dosen = Dosen::where('nama_lengkap', 'LIKE', '%' . $row['dosen_wali_id'] . '%')->first();
                $dosenWaliId = $dosen ? $dosen->id : null;
            }
        }

        // 4. Parsing Tanggal Lahir
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

        // 5. MANAJEMEN USER (FIXED: UPDATE OR CREATE)
        // Gunakan updateOrCreate agar user yang sudah ada rolenya diperbaiki
        $email = !empty($row['email']) ? trim($row['email']) : $nim . '@student.sttgpipapua.ac.id';
        $password = !empty($row['password']) ? Hash::make($row['password']) : Hash::make($nim);

        $user = User::updateOrCreate(
            ['email' => $email], // Kunci pencarian
            [
                'name' => $row['nama_lengkap'],
                'password' => $password,
                'role_id' => $roleId // PAKSA UPDATE ROLE DISINI
            ]
        );

        // Tambahan: Jika pakai Spatie Permission, paksa assign role via method ini
        if ($role && method_exists($user, 'assignRole')) {
            $user->assignRole($role->name);
        }

        // 6. Simpan / Update Data Mahasiswa
        return Mahasiswa::updateOrCreate(
            ['nim' => $nim],
            [
                'user_id'           => $user->id,
                'nama_lengkap'      => $row['nama_lengkap'],
                'program_studi_id'  => $prodiId,
                'dosen_wali_id'     => $dosenWaliId,
                'angkatan'          => $row['tahun_masuk'] ?? date('Y'),
                'status_mahasiswa'  => $row['status_mahasiswa'] ?? 'Aktif',
                'nik'               => $row['nik'] ?? null,
                'nisn'              => $row['nisn'] ?? null,
                'jalur_pendaftaran' => $row['jalur_pendaftaran'] ?? 'Mandiri',
                'tempat_lahir'      => $row['tempat_lahir'] ?? null,
                'tanggal_lahir'     => $tanggalLahir,
                'jenis_kelamin'     => $row['jenis_kelamin'] ?? 'L',
                'agama'             => $row['agama'] ?? 'Kristen Protestan',
                'nomor_telepon'     => $row['nomor_telepon'] ?? null,
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