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
    private $dosenRole;

    public function __construct()
    {
        $this->dosenRole = Role::where('name', 'dosen')->first();
    }

    public function headingRow(): int
    {
        return 2; // Header ada di baris 2
    }

    public function model(array $row)
    {
        // 1. Skip baris jika data utama kosong
        if (empty($row['nidn']) || empty($row['nama_lengkap'])) {
            return null;
        }

        // 2. Sanitasi Data
        $nidn = trim((string) $row['nidn']);
        $email = !empty($row['email']) ? trim($row['email']) : $nidn . '@lecturer.sttgpipapua.ac.id';
        $password = !empty($row['password']) ? Hash::make($row['password']) : Hash::make($nidn); // Default pass NIDN

        // 3. Parsing Tanggal Lahir & TMT
        $tanggalLahir = $this->parseDate($row['tanggal_lahir'] ?? null);
        $tmtSk = $this->parseDate($row['tmt_sk_pengangkatan'] ?? null);

        // 4. Buat User Login
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $row['nama_lengkap'],
                'password' => $password,
                'role_id' => $this->dosenRole->id ?? 2 // Default role ID 2 (Dosen)
            ]
        );

        // 5. Simpan Data Dosen
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

    // Helper Parse Date
    private function parseDate($value) {
        if (empty($value)) return null;
        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } else {
                return Carbon::parse($value)->format('Y-m-d');
            }
        } catch (\Exception $e) { return null; }
    }

    public function rules(): array
    {
        return [
            'nidn' => 'required',
            'nama_lengkap' => 'required',
        ];
    }
}