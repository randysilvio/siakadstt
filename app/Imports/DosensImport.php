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
        return 1; 
    }

    public function model(array $row)
    {
        // [MINOR UPDATE] Mengubah syarat wajib dari NIDN menjadi NIK agar Asisten Dosen bisa diimpor
        if (empty($row['nik']) || empty($row['nama_lengkap'])) {
            return null;
        }

        // Tangkap NIDN jika ada, jadikan null jika kosong
        $nidn = !empty($row['nidn']) ? trim((string) $row['nidn']) : null;
        
        // Tangkap NIK (Dipastikan ada karena lolos pengecekan di atas)
        $nik = trim((string) $row['nik']);

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

        // [MINOR UPDATE] Logika Fallback Aman (Gunakan NIK jika NIDN Kosong)
        $fallbackUsername = $nidn ?: $nik;

        $email = !empty($row['email']) ? trim($row['email']) : $fallbackUsername . '@lecturer.sttgpipapua.ac.id';
        $password = !empty($row['password']) ? Hash::make($row['password']) : Hash::make($fallbackUsername);

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

        // [MINOR UPDATE] Pivot pencarian updateOrCreate diganti dari NIDN menjadi NIK untuk mencegah Data Overwrite
        return Dosen::updateOrCreate(
            ['nik' => $nik],
            [
                'user_id'             => $user->id,
                'nidn'                => $nidn, // NIDN tetap disimpan jika ada nilainya
                'program_studi_id'    => $prodiId, 
                'nama_lengkap'        => $row['nama_lengkap'],
                'nuptk'               => $row['nuptk'] ?? null,
                'npwp'                => $row['npwp'] ?? null,
                
                'tempat_lahir'        => $row['tempat_lahir'] ?? null,
                'tanggal_lahir'       => $tanggalLahir,
                'jenis_kelamin'       => $row['jenis_kelamin'] ?? 'L',
                'agama'               => $row['agama'] ?? 'Kristen Protestan',
                'alamat'              => $row['alamat'] ?? null,
                
                // [MINOR UPDATE] Menambahkan kategori pengajar jika diisi di excel, default Dosen Tetap
                'jenis_pengajar'      => $row['jenis_pengajar'] ?? 'Dosen Tetap', 
                
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
            // [MINOR UPDATE] Validasi excel mengikuti pivot pencarian
            'nik' => 'required',
            'nama_lengkap' => 'required',
        ];
    }
}