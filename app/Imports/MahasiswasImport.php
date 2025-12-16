<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class MahasiswasImport implements ToModel, WithHeadingRow, WithValidation
{
    private $mahasiswaRole;

    public function __construct()
    {
        $this->mahasiswaRole = Role::where('name', 'mahasiswa')->first();
    }

    public function model(array $row)
    {
        $nim = trim((string) $row['nim']);
        $tahunMasuk = trim((string) $row['tahun_masuk']);
        $noTelp = trim((string) $row['nomor_telepon']);

        // Handle Tanggal Lahir (Excel Serial vs Text)
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

        $mahasiswa = null; 
        
        DB::transaction(function () use ($row, $nim, $tahunMasuk, $noTelp, $tanggalLahir, &$mahasiswa) {
            $user = User::firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['nama_lengkap'],
                    'password' => Hash::make($row['password']),
                ]
            );

            if ($this->mahasiswaRole) {
                $user->roles()->syncWithoutDetaching($this->mahasiswaRole->id);
            }

            $mahasiswa = Mahasiswa::updateOrCreate(
                ['nim' => $nim],
                [
                    'user_id'           => $user->id,
                    'nama_lengkap'      => $row['nama_lengkap'],
                    'program_studi_id'  => $row['program_studi_id'],
                    'dosen_wali_id'     => $row['dosen_wali_id'],
                    'tahun_masuk'       => $tahunMasuk,
                    'tempat_lahir'      => $row['tempat_lahir'],
                    'tanggal_lahir'     => $tanggalLahir,
                    'jenis_kelamin'     => $row['jenis_kelamin'],
                    'alamat'            => $row['alamat'],
                    'nomor_telepon'     => $noTelp,
                    'status_mahasiswa'  => 'Aktif',
                ]
            );
        });
        
        return $mahasiswa;
    }

    public function rules(): array
    {
        return [
            'nim'               => 'required', 
            'nama_lengkap'      => 'required',
            'program_studi_id'  => 'required|integer',
            'email'             => 'required|email', 
            'password'          => 'required|min:6',
            'tahun_masuk'       => 'required',
        ];
    }
}