<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
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

    public function model(array $row)
    {
        // Skip baris kosong
        if (empty($row['nidn']) || empty($row['email'])) {
            return null;
        }

        // Handle Date
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
        
        $tmtSk = null;
        if (!empty($row['tmt_sk_pengangkatan'])) {
            try {
                if (is_numeric($row['tmt_sk_pengangkatan'])) {
                    $tmtSk = Date::excelToDateTimeObject($row['tmt_sk_pengangkatan'])->format('Y-m-d');
                } else {
                    $tmtSk = Carbon::parse($row['tmt_sk_pengangkatan'])->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $tmtSk = null;
            }
        }

        return DB::transaction(function () use ($row, $tanggalLahir, $tmtSk) {
            $email = trim($row['email']);
            $nidn = trim((string) $row['nidn']);
            
            // 1. User Logic
            $user = User::where('email', $email)->first();
            if ($user) {
                $dataUpdate = ['name' => $row['nama_lengkap']];
                if (!empty($row['password'])) {
                    $dataUpdate['password'] = Hash::make($row['password']);
                }
                $user->update($dataUpdate);
            } else {
                $user = User::create([
                    'name' => $row['nama_lengkap'],
                    'email' => $email,
                    'password' => Hash::make($row['password']),
                ]);
            }

            if ($this->dosenRole) {
                $user->roles()->syncWithoutDetaching($this->dosenRole->id);
            }

            // 2. Dosen Profile Logic (Lengkap)
            return Dosen::updateOrCreate(
                ['nidn' => $nidn],
                [
                    'user_id'             => $user->id,
                    'nama_lengkap'        => $row['nama_lengkap'],
                    'nik'                 => $row['nik'] ?? null,
                    'nuptk'               => $row['nuptk'] ?? null,
                    'npwp'                => $row['npwp'] ?? null,
                    'tempat_lahir'        => $row['tempat_lahir'] ?? null,
                    'tanggal_lahir'       => $tanggalLahir,
                    'jenis_kelamin'       => $row['jenis_kelamin'] ?? null,
                    'alamat'              => $row['alamat'] ?? null,
                    'status_kepegawaian'  => $row['status_kepegawaian'] ?? 'Dosen Tetap',
                    'no_sk_pengangkatan'  => $row['no_sk_pengangkatan'] ?? null,
                    'tmt_sk_pengangkatan' => $tmtSk,
                    'pangkat_golongan'    => $row['pangkat_golongan'] ?? null,
                    'jabatan_akademik'    => $row['jabatan_akademik'] ?? null,
                    'bidang_keahlian'     => $row['bidang_keahlian'] ?? null,
                    'email_institusi'     => $row['email_institusi'] ?? null,
                    'link_google_scholar' => $row['link_google_scholar'] ?? null,
                    'link_sinta'          => $row['link_sinta'] ?? null,
                ]
            );
        });
    }

    public function rules(): array
    {
        return [
            'nidn' => 'required',
            'nama_lengkap' => 'required',
            'email' => 'required|email',
        ];
    }
}