<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class MahasiswasImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    private $mahasiswaRole;

    public function __construct()
    {
        $this->mahasiswaRole = Role::where('name', 'mahasiswa')->first();
    }

    public function model(array $row)
    {
        // 1. Skip baris kosong
        if (empty($row['nim']) || empty($row['nama_lengkap'])) {
            return null;
        }

        // 2. Sanitasi Data
        $nim = trim((string) $row['nim']);
        $email = trim($row['email']);
        $nik = isset($row['nik']) ? trim((string) $row['nik']) : null; // NIK Wajib untuk Feeder
        
        // Sanitasi Dosen Wali (0 atau '-' jadi null)
        $dosenWaliId = $row['dosen_wali_id'];
        if (empty($dosenWaliId) || $dosenWaliId == '0' || $dosenWaliId == '-') {
            $dosenWaliId = null;
        }

        // Handle Tanggal Lahir
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

        return DB::transaction(function () use ($row, $nim, $email, $nik, $tanggalLahir, $dosenWaliId) {
            // A. UPDATE / CREATE USER
            $user = User::where('email', $email)->first();
            
            if ($user) {
                $userData = ['name' => $row['nama_lengkap']];
                if (!empty($row['password'])) {
                    $userData['password'] = Hash::make($row['password']);
                }
                $user->update($userData);
            } else {
                $user = User::create([
                    'name' => $row['nama_lengkap'],
                    'email' => $email,
                    'password' => Hash::make($row['password']),
                ]);
            }

            if ($this->mahasiswaRole) {
                $user->roles()->syncWithoutDetaching($this->mahasiswaRole->id);
            }

            // B. UPDATE / CREATE MAHASISWA
            return Mahasiswa::updateOrCreate(
                ['nim' => $nim], 
                [
                    'user_id'           => $user->id,
                    'program_studi_id'  => $row['program_studi_id'],
                    'dosen_wali_id'     => $dosenWaliId,
                    'nama_lengkap'      => $row['nama_lengkap'],
                    'tahun_masuk'       => $row['tahun_masuk'],
                    'status_mahasiswa'  => $row['status_mahasiswa'] ?? 'Aktif',
                    
                    // --- DATA PRIBADI ---
                    'nik'               => $nik,
                    'nisn'              => $row['nisn'] ?? null,
                    'kewarganegaraan'   => $row['kewarganegaraan'] ?? 'WNI',
                    'jalur_pendaftaran' => $row['jalur_pendaftaran'] ?? 'Mandiri',
                    'tempat_lahir'      => $row['tempat_lahir'] ?? null,
                    'tanggal_lahir'     => $tanggalLahir,
                    'jenis_kelamin'     => $row['jenis_kelamin'] ?? null,
                    'agama'             => $row['agama'] ?? null,
                    'nomor_telepon'     => $row['nomor_telepon'] ?? null,

                    // --- ALAMAT DETAIL ---
                    'alamat'            => $row['alamat'] ?? null,
                    'dusun'             => $row['dusun'] ?? null,
                    'rt'                => $row['rt'] ?? null,
                    'rw'                => $row['rw'] ?? null,
                    'kelurahan'         => $row['kelurahan'] ?? null,
                    'kecamatan'         => $row['kecamatan'] ?? null,
                    'kode_pos'          => $row['kode_pos'] ?? null,
                    'jenis_tinggal'     => $row['jenis_tinggal'] ?? null,
                    'alat_transportasi' => $row['alat_transportasi'] ?? null,

                    // --- DATA ORANG TUA ---
                    'nama_ibu_kandung'  => $row['nama_ibu_kandung'], // Wajib
                    'nik_ibu'           => $row['nik_ibu'] ?? null,
                    'pendidikan_ibu'    => $row['pendidikan_ibu'] ?? null,
                    'pekerjaan_ibu'     => $row['pekerjaan_ibu'] ?? null,
                    'penghasilan_ibu'   => $row['penghasilan_ibu'] ?? null,
                    
                    'nama_ayah'         => $row['nama_ayah'] ?? null,
                    'nik_ayah'          => $row['nik_ayah'] ?? null,
                    'pendidikan_ayah'   => $row['pendidikan_ayah'] ?? null,
                    'pekerjaan_ayah'    => $row['pekerjaan_ayah'] ?? null,
                    'penghasilan_ayah'  => $row['penghasilan_ayah'] ?? null,
                    
                    'nama_wali'         => $row['nama_wali'] ?? null,
                    'pekerjaan_wali'    => $row['pekerjaan_wali'] ?? null,
                ]
            );
        });
    }

    public function rules(): array
    {
        return [
            'nim'               => 'required', 
            'nama_lengkap'      => 'required',
            'program_studi_id'  => 'required|exists:program_studis,id', 
            'email'             => 'required|email',
            'nama_ibu_kandung'  => 'required', // Wajib Feeder
        ];
    }
}