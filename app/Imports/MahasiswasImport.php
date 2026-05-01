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
        return 1; 
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

        // 3. Setup Prodi
        $prodiId = null;
        $inputProdi = !empty($row['program_studi']) ? $row['program_studi'] : (!empty($row['program_studi_id']) ? $row['program_studi_id'] : null);

        if (!empty($inputProdi)) {
            if (is_numeric($inputProdi)) {
                $prodiId = $inputProdi;
            } else {
                $prodi = ProgramStudi::where('nama_prodi', 'LIKE', '%' . $inputProdi . '%')->first();
                $prodiId = $prodi ? $prodi->id : null;
            }
        }

        // Default Prodi jika Kosong
        if (empty($prodiId)) {
            $prodiId = 1; 
        }

        // 4. Setup Dosen Wali
        $dosenWaliId = null;
        $inputDosen = !empty($row['dosen_wali']) ? $row['dosen_wali'] : (!empty($row['dosen_wali_id']) ? $row['dosen_wali_id'] : null);

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

        // 7. Simpan Data Mahasiswa (Disesuaikan dengan field database yang asli)
        // Menambahkan fungsi ltrim dan trim untuk membuang tanda petik pelindung excel
        return Mahasiswa::updateOrCreate(
            ['nim' => $nim],
            [
                'user_id'           => $user->id,
                'nama_lengkap'      => $row['nama_lengkap'],
                'program_studi_id'  => $prodiId, 
                'dosen_wali_id'     => $dosenWaliId,
                'tahun_masuk'       => !empty($row['angkatan']) ? trim($row['angkatan']) : (!empty($row['tahun_masuk']) ? trim($row['tahun_masuk']) : date('Y')),
                'status_mahasiswa'  => !empty($row['status_mahasiswa']) ? trim($row['status_mahasiswa']) : 'Aktif',
                
                // MEMBERSIHKAN NIK DAN NISN DARI TANDA PETIK (') DAN SPASI
                'nik'               => !empty($row['nik']) ? ltrim(trim((string)$row['nik']), "'") : null,
                'nisn'              => !empty($row['nisn']) ? ltrim(trim((string)$row['nisn']), "'") : null,
                
                'jalur_pendaftaran' => !empty($row['jalur_pendaftaran']) ? trim($row['jalur_pendaftaran']) : 'Mandiri',
                'tempat_lahir'      => !empty($row['tempat_lahir']) ? trim($row['tempat_lahir']) : null,
                'tanggal_lahir'     => $tanggalLahir,
                'jenis_kelamin'     => !empty($row['jenis_kelamin']) ? strtoupper(trim($row['jenis_kelamin'])) : 'L',
                'agama'             => !empty($row['agama']) ? trim($row['agama']) : 'Kristen Protestan',
                
                // MEMBERSIHKAN NOMOR TELEPON
                'nomor_telepon'     => !empty($row['no_hp']) ? ltrim(trim((string)$row['no_hp']), "'") : (!empty($row['nomor_telepon']) ? ltrim(trim((string)$row['nomor_telepon']), "'") : null),
                
                'alamat'            => !empty($row['alamat']) ? trim($row['alamat']) : null,
                'nama_ibu_kandung'  => !empty($row['nama_ibu_kandung']) ? trim($row['nama_ibu_kandung']) : null,
                'nama_ayah'         => !empty($row['nama_ayah']) ? trim($row['nama_ayah']) : null,
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