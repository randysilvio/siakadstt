<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\User;
use App\Models\Role;
use App\Models\ProgramStudi;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class DosensImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public function headingRow(): int
    {
        return 2; 
    }

    public function model(array $row)
    {
        if (empty($row['nidn']) || empty($row['nama_lengkap'])) {
            return null;
        }

        $nidn = trim((string) $row['nidn']);

        $role = Role::where('name', 'LIKE', 'dosen')->orWhere('name', 'LIKE', 'lecturer')->first();
        $roleId = $role ? $role->id : 2; 

        $tanggalLahir = $this->parseDate($row['tanggal_lahir'] ?? null);
        $tmtSk = $this->parseDate($row['tmt_sk_pengangkatan'] ?? null);

        // --- Cek ID Program Studi berdasarkan teks yang diinput di Excel ---
        $prodiId = null;
        if (!empty($row['program_studi'])) {
            $prodi = ProgramStudi::where('nama_prodi', 'LIKE', '%' . trim($row['program_studi']) . '%')->first();
            if ($prodi) {
                $prodiId = $prodi->id;
            }
        }

        $email = !empty($row['email']) ? trim($row['email']) : $nidn . '@lecturer.sttgpipapua.ac.id';
        $password = !empty($row['password']) ? Hash::make($row['password']) : Hash::make($nidn);

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

        return Dosen::updateOrCreate(
            ['nidn' => $nidn],
            [
                'user_id'             => $user->id,
                'program_studi_id'    => $prodiId, // <-- Tambahkan Relasi Prodi
                'nama_lengkap'        => $row['nama_lengkap'],
                'nik'                 => $row['nik'] ?? null,
                'nuptk'               => $row['nuptk'] ?? null,
                'npwp'                => $row['npwp'] ?? null,
                
                'tempat_lahir'        => $row['tempat_lahir'] ?? null,
                'tanggal_lahir'       => $tanggalLahir,
                'jenis_kelamin'       => $row['jenis_kelamin'] ?? 'L',
                'agama'               => $row['agama'] ?? 'Kristen Protestan',
                'alamat'              => $row['alamat'] ?? null,
                
                'status_kepegawaian'  => $row['status_kepegawaian'] ?? 'Dosen Tetap',
                'no_sk_pengangkatan'  => $row['no_sk_pengangkatan'] ?? null,
                'tmt_sk_pengangkatan' => $tmtSk,
                'pangkat_golongan'    => $row['pangkat_golongan'] ?? null,
                'jabatan_akademik'    => $row['jabatan_akademik'] ?? null,
                'bidang_keahlian'     => $row['bidang_keahlian'] ?? null,
                'email_institusi'     => $row['email_institusi'] ?? $email,
                
                'link_google_scholar' => $row['link_google_scholar'] ?? null,
                'link_sinta'          => $row['link_sinta'] ?? null,
            ]
        );
    }

    private function parseDate($value) {
        if (empty($value) || $value == '-' || $value == '0') return null;
        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } else {
                return Carbon::parse($value)->format('Y-m-d');
            }
        } catch (\Exception $e) { 
            return null; 
        }
    }

    public function rules(): array
    {
        return [
            'nidn' => 'required',
            'nama_lengkap' => 'required',
        ];
    }
}