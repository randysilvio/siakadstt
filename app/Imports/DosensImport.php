<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\User;
use App\Models\Role;
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
        return 2; // Baris 1: Judul Template, Baris 2: Header Kolom
    }

    public function model(array $row)
    {
        // 1. Validasi Data Utama (NIDN & Nama Wajib Ada)
        if (empty($row['nidn']) || empty($row['nama_lengkap'])) {
            return null;
        }

        $nidn = trim((string) $row['nidn']);

        // 2. Cari Role Dosen (Case Insensitive)
        $role = Role::where('name', 'LIKE', 'dosen')
                    ->orWhere('name', 'LIKE', 'lecturer')
                    ->first();
        // Default ID 2 (sesuaikan dengan DB Anda, biasanya: 1=Admin, 2=Dosen, 3=Mhs)
        $roleId = $role ? $role->id : 2; 

        // 3. Parsing Tanggal (Tanggal Lahir & TMT)
        $tanggalLahir = $this->parseDate($row['tanggal_lahir'] ?? null);
        $tmtSk = $this->parseDate($row['tmt_sk_pengangkatan'] ?? null);

        // 4. MANAJEMEN USER (Update or Create)
        // Jika email kosong di CSV, buat email dummy dari NIDN
        $email = !empty($row['email']) ? trim($row['email']) : $nidn . '@lecturer.sttgpipapua.ac.id';
        
        // Password default = NIDN (jika di CSV kosong)
        $password = !empty($row['password']) ? Hash::make($row['password']) : Hash::make($nidn);

        // Paksa Update User agar Role-nya masuk/diperbaiki
        $user = User::updateOrCreate(
            ['email' => $email], // Kunci pencarian
            [
                'name' => $row['nama_lengkap'],
                'password' => $password,
                'role_id' => $roleId // PAKSA ISI ROLE DOSEN
            ]
        );

        // Support untuk library Spatie Permission (Jaga-jaga)
        if ($role && method_exists($user, 'assignRole')) {
            $user->assignRole($role->name);
        }

        // 5. Simpan / Update Data Dosen
        return Dosen::updateOrCreate(
            ['nidn' => $nidn],
            [
                'user_id'             => $user->id,
                'nama_lengkap'        => $row['nama_lengkap'],
                'nik'                 => $row['nik'] ?? null,
                'nuptk'               => $row['nuptk'] ?? null,
                'npwp'                => $row['npwp'] ?? null,
                
                // Biodata
                'tempat_lahir'        => $row['tempat_lahir'] ?? null,
                'tanggal_lahir'       => $tanggalLahir,
                'jenis_kelamin'       => $row['jenis_kelamin'] ?? 'L',
                'agama'               => $row['agama'] ?? 'Kristen Protestan',
                'alamat'              => $row['alamat'] ?? null,
                
                // Kepegawaian
                'status_kepegawaian'  => $row['status_kepegawaian'] ?? 'Dosen Tetap',
                'no_sk_pengangkatan'  => $row['no_sk_pengangkatan'] ?? null,
                'tmt_sk_pengangkatan' => $tmtSk,
                'pangkat_golongan'    => $row['pangkat_golongan'] ?? null,
                'jabatan_akademik'    => $row['jabatan_akademik'] ?? null,
                'bidang_keahlian'     => $row['bidang_keahlian'] ?? null,
                'email_institusi'     => $row['email_institusi'] ?? $email,
                
                // Link Eksternal
                'link_google_scholar' => $row['link_google_scholar'] ?? null,
                'link_sinta'          => $row['link_sinta'] ?? null,
            ]
        );
    }

    /**
     * Helper untuk mengubah format tanggal Excel/String ke format MySQL (Y-m-d)
     */
    private function parseDate($value) {
        if (empty($value) || $value == '-' || $value == '0') return null;
        try {
            if (is_numeric($value)) {
                // Jika format angka Excel (Serial Date)
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } else {
                // Jika format teks (misal: 1990-01-01 atau 01/01/1990)
                return Carbon::parse($value)->format('Y-m-d');
            }
        } catch (\Exception $e) { 
            return null; // Jika gagal parsing, biarkan null
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