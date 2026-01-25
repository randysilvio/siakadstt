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
    private $mahasiswaRole;

    public function __construct()
    {
        $this->mahasiswaRole = Role::where('name', 'mahasiswa')->first();
    }

    public function headingRow(): int
    {
        return 2; // Header ada di baris 2
    }

    public function model(array $row)
    {
        // 1. Skip jika data utama kosong
        if (empty($row['nim']) || empty($row['nama_lengkap'])) {
            return null;
        }

        // 2. LOGIKA CERDAS PROGRAM STUDI
        $prodiId = null;
        if (!empty($row['program_studi'])) {
            // Jika input ANGKA (ID), langsung pakai (Anti Typo)
            if (is_numeric($row['program_studi'])) {
                $prodiId = $row['program_studi'];
            } 
            // Jika input TEKS (Nama), cari yang mirip
            else {
                $prodi = ProgramStudi::where('nama_prodi', 'LIKE', '%' . $row['program_studi'] . '%')->first();
                $prodiId = $prodi ? $prodi->id : null; 
            }
        }

        // 3. LOGIKA CERDAS DOSEN WALI
        $dosenWaliId = null;
        if (!empty($row['dosen_wali'])) {
            // Jika input ANGKA (ID), langsung pakai
            if (is_numeric($row['dosen_wali'])) {
                $dosenWaliId = $row['dosen_wali'];
            } 
            // Jika input TEKS (Nama), cari yang mirip
            else {
                $dosen = Dosen::where('nama_lengkap', 'LIKE', '%' . $row['dosen_wali'] . '%')->first();
                $dosenWaliId = $dosen ? $dosen->id : null;
            }
        }

        // 4. Sanitasi Data Lain
        $nim = trim((string) $row['nim']);
        $email = !empty($row['email']) ? trim($row['email']) : $nim . '@student.sttgpipapua.ac.id';
        $password = !empty($row['password']) ? Hash::make($row['password']) : Hash::make($nim);

        // Parsing Tanggal Lahir (Excel Number vs String)
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

        // 5. Buat User Login
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $row['nama_lengkap'],
                'password' => $password,
                'role_id' => $this->mahasiswaRole->id ?? 3
            ]
        );

        // 6. Simpan Mahasiswa
        return Mahasiswa::updateOrCreate(
            ['nim' => $nim],
            [
                'user_id'           => $user->id,
                'nama_lengkap'      => $row['nama_lengkap'],
                'program_studi_id'  => $prodiId, // Hasil logika cerdas di atas
                'dosen_wali_id'     => $dosenWaliId, // Hasil logika cerdas di atas
                'angkatan'          => $row['angkatan'] ?? date('Y'),
                'status_mahasiswa'  => $row['status_mahasiswa'] ?? 'Aktif',
                
                // Identitas
                'nik'               => $row['nik'] ?? null,
                'nisn'              => $row['nisn'] ?? null,
                'jalur_pendaftaran' => $row['jalur_pendaftaran'] ?? 'Mandiri',
                
                // Biodata
                'tempat_lahir'      => $row['tempat_lahir'] ?? null,
                'tanggal_lahir'     => $tanggalLahir,
                'jenis_kelamin'     => $row['jenis_kelamin'] ?? 'L',
                'agama'             => $row['agama'] ?? 'Kristen Protestan',
                'nomor_telepon'     => $row['no_hp'] ?? null,
                
                // Alamat
                'alamat'            => $row['alamat'] ?? null,
                'dusun'             => $row['dusun'] ?? null,
                'rt'                => $row['rt'] ?? null,
                'rw'                => $row['rw'] ?? null,
                'kelurahan'         => $row['kelurahan'] ?? null,
                'kecamatan'         => $row['kecamatan'] ?? null,
                'kode_pos'          => $row['kode_pos'] ?? null,
                'jenis_tinggal'     => $row['jenis_tinggal'] ?? null,
                'alat_transportasi' => $row['alat_transportasi'] ?? null,

                // Orang Tua & Wali
                'nama_ibu_kandung'  => $row['nama_ibu_kandung'] ?? null,
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
    }

    public function rules(): array
    {
        return [
            'nim' => 'required',
            'nama_lengkap' => 'required',
        ];
    }
}