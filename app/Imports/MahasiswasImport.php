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
use App\Imports\Concerns\ResolveReferensiImpor;

class MahasiswasImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    use ResolveReferensiImpor;

    public function headingRow(): int
    {
        // [PERBAIKAN BUG FASE 5]: Membaca judul kolom dari baris ke-2 (baris 1 adalah banner peringatan template)
        return 2; 
    }

    public function model(array $row)
    {
        $this->majukanBaris();

        if (empty($row['nim']) || empty($row['nama_lengkap'])) {
            return null;
        }

        $nim = ltrim(trim((string) $row['nim']), "'");

        $role = Role::where('name', 'mahasiswa')->first();

        // Prodi WAJIB cocok persis (ID / kode / nama). Tidak ada lagi fallback ke prodi pertama.
        $inputProdi = !empty($row['program_studi']) ? $row['program_studi'] : ($row['program_studi_id'] ?? null);
        $prodi = $this->cariProdi($inputProdi);
        if (!$prodi) {
            $this->gagal("program studi '" . trim((string) $inputProdi) . "' tidak ditemukan. Gunakan kode atau nama prodi persis seperti di menu Program Studi.");
        }
        $prodiId = $prodi->id;

        $dosenWaliId = null;
        $inputDosen = !empty($row['dosen_wali']) ? $row['dosen_wali'] : ($row['dosen_wali_id'] ?? null);
        if (!empty($inputDosen)) {
            $dosen = $this->cariDosen($inputDosen);
            if (!$dosen) {
                $this->gagal("dosen wali '" . trim((string) $inputDosen) . "' tidak ditemukan. Gunakan NIDN atau nama lengkap persis.");
            }
            $dosenWaliId = $dosen->id;
        }

        $statusMahasiswa = 'Aktif';
        if (!empty($row['status_mahasiswa'])) {
            $statusMahasiswa = Mahasiswa::normalisasiStatus($row['status_mahasiswa'])
                ?? $this->gagal("status mahasiswa '" . trim((string) $row['status_mahasiswa']) . "' tidak dikenal. Pilihan: " . implode(', ', Mahasiswa::STATUS) . '.');
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

        // Akun staf/dosen/admin tidak boleh ditimpa oleh baris impor mahasiswa yang emailnya bentrok
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && !$this->akunBolehDipakai($existingUser, ['mahasiswa'])) {
            $this->gagal("email {$email} sudah dipakai akun non-mahasiswa.");
        }

        if ($existingUser) {
            $user = $existingUser;
            $user->name = $row['nama_lengkap'];
            if (!empty($row['password'])) {
                $user->password = Hash::make($this->passwordImpor($row['password']));
            }
            $user->save();
        } else {
            // Password default tidak lagi = NIM (data publik). Kosong => acak; mahasiswa memakai "Lupa Password".
            $user = User::create([
                'name' => $row['nama_lengkap'],
                'email' => $email,
                'password' => Hash::make($this->passwordImpor($row['password'] ?? '')),
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
                'status_mahasiswa'  => $statusMahasiswa,
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