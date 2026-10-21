<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\User;
use App\Models\Role;
use App\Models\ProgramStudi;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
        // [PERBAIKAN BUG FASE 5]: Baris 1 adalah banner judul template, header kolom berada di baris 2
        return 2; 
    }

    public function model(array $row)
    {
        if (empty($row['nik']) || empty($row['nama_lengkap'])) {
            return null;
        }

        $nidn = !empty($row['nidn']) ? ltrim(trim((string) $row['nidn']), "'") : null;
        $nik = ltrim(trim((string) $row['nik']), "'");

        $role = Role::where('name', 'LIKE', 'dosen')->orWhere('name', 'LIKE', 'lecturer')->first();

        $tanggalLahir = $this->parseDate($row['tanggal_lahir'] ?? null);
        $tmtSk = $this->parseDate($row['tmt_sk_pengangkatan'] ?? null);

        $prodiId = null;
        if (!empty($row['program_studi'])) {
            $prodi = ProgramStudi::where('nama_prodi', 'LIKE', '%' . trim($row['program_studi']) . '%')->first();
            if ($prodi) {
                $prodiId = $prodi->id;
            }
        }

        $fallbackUsername = $nidn ?: $nik;
        $email = !empty($row['email']) ? trim($row['email']) : $fallbackUsername . '@lecturer.sttgpipapua.ac.id';

        // [PERBAIKAN KEAMANAN FASE 5]: Cegah penimpaan akun Admin jika email bentrok
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $existingUser->hasRole('admin')) {
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
            $rawPassword = !empty($row['password']) ? (string) $row['password'] : ($fallbackUsername . '@' . Str::upper(Str::random(4)));
            $user = User::create([
                'name' => $row['nama_lengkap'],
                'email' => $email,
                'password' => Hash::make($rawPassword),
            ]);
        }

        if ($role && method_exists($user, 'assignRole')) {
            $user->assignRole($role->name);
        }

        return Dosen::updateOrCreate(
            ['nik' => $nik],
            [
                'user_id'             => $user->id,
                'nidn'                => $nidn,
                'program_studi_id'    => $prodiId, 
                'nama_lengkap'        => $row['nama_lengkap'],
                'nuptk'               => !empty($row['nuptk']) ? ltrim(trim((string) $row['nuptk']), "'") : null,
                'npwp'                => !empty($row['npwp']) ? ltrim(trim((string) $row['npwp']), "'") : null,
                'tempat_lahir'        => $row['tempat_lahir'] ?? null,
                'tanggal_lahir'       => $tanggalLahir,
                'jenis_kelamin'       => $row['jenis_kelamin'] ?? 'L',
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

    private function parseDate($value)
    {
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
            'nik' => 'required',
            'nama_lengkap' => 'required',
        ];
    }
}