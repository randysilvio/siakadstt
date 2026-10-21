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
        // [PERBAIKAN BUG FASE 5]: Membaca judul kolom dari baris ke-2 (baris 1 adalah banner peringatan template)
        return 2; 
    }

    public function model(array $row)
    {
        if (empty($row['nim']) || empty($row['nama_lengkap'])) {
            return null;
        }

        $nim = ltrim(trim((string) $row['nim']), "'");

        $role = Role::where('name', 'LIKE', 'mahasiswa')
                    ->orWhere('name', 'LIKE', 'student')
                    ->first();

        $prodiId = null;
        $inputProdi = !empty($row['program_studi']) ? $row['program_studi'] : (!empty($row['program_studi_id']) ? $row['program_studi_id'] : null);

        if (!empty($inputProdi)) {
            if (is_numeric($inputProdi)) {
                $prodiId = (int) $inputProdi;
            } else {
                $prodi = ProgramStudi::where('nama_prodi', 'LIKE', '%' . trim($inputProdi) . '%')->first();
                $prodiId = $prodi ? $prodi->id : null;
            }
        }

        if (empty($prodiId)) {
            $firstProdi = ProgramStudi::first();
            $prodiId = $firstProdi ? $firstProdi->id : 1; 
        }

        $dosenWaliId = null;
        $inputDosen = !empty($row['dosen_wali']) ? $row['dosen_wali'] : (!empty($row['dosen_wali_id']) ? $row['dosen_wali_id'] : null);

        if (!empty($inputDosen)) {
            if (is_numeric($inputDosen)) {
                $dosenWaliId = (int) $inputDosen;
            } else {
                $dosen = Dosen::where('nama_lengkap', 'LIKE', '%' . trim($inputDosen) . '%')->first();
                $dosenWaliId = $dosen ? $dosen->id : null;
            }
        }

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

        $email = !empty($row['email']) ? trim($row['email']) : $nim . '@student.sttgpipapua.ac.id';

        // [PERBAIKAN KEAMANAN FASE 5]: Cegah penimpaan akun Admin atau Dosen jika email bentrok
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $existingUser->hasRole(['admin', 'dosen'])) {
            return null;
        }

        if ($existingUser) {
            $user = $existingUser;
            $user->name = $row['nama_lengkap'];
            if (!empty($row['password'])) {
                $user->password = Hash::make((string) $row['password']);
            }
            $user->save();
        } else {
            $password = !empty($row['password']) ? Hash::make((string) $row['password']) : Hash::make($nim);
            $user = User::create([
                'name' => $row['nama_lengkap'],
                'email' => $email,
                'password' => $password,
            ]);
        }

        if ($role && method_exists($user, 'assignRole')) {
            $user->assignRole($role->name);
        }

        return Mahasiswa::updateOrCreate(
            ['nim' => $nim],
            [
                'user_id'           => $user->id,
                'nama_lengkap'      => $row['nama_lengkap'],
                'program_studi_id'  => $prodiId, 
                'dosen_wali_id'     => $dosenWaliId,
                'tahun_masuk'       => !empty($row['angkatan']) ? trim((string) $row['angkatan']) : (!empty($row['tahun_masuk']) ? trim((string) $row['tahun_masuk']) : date('Y')),
                'status_mahasiswa'  => !empty($row['status_mahasiswa']) ? trim($row['status_mahasiswa']) : 'Aktif',
                'nik'               => !empty($row['nik']) ? ltrim(trim((string) $row['nik']), "'") : null,
                'nisn'              => !empty($row['nisn']) ? ltrim(trim((string) $row['nisn']), "'") : null,
                'jalur_pendaftaran' => !empty($row['jalur_pendaftaran']) ? trim($row['jalur_pendaftaran']) : 'Mandiri',
                'tempat_lahir'      => !empty($row['tempat_lahir']) ? trim($row['tempat_lahir']) : null,
                'tanggal_lahir'     => $tanggalLahir,
                'jenis_kelamin'     => !empty($row['jenis_kelamin']) ? strtoupper(trim($row['jenis_kelamin'])) : 'L',
                'agama'             => !empty($row['agama']) ? trim($row['agama']) : 'Kristen Protestan',
                'nomor_telepon'     => !empty($row['no_hp']) ? ltrim(trim((string) $row['no_hp']), "'") : (!empty($row['nomor_telepon']) ? ltrim(trim((string) $row['nomor_telepon']), "'") : null),
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