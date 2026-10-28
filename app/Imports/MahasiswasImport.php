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

        $mahasiswaLama = Mahasiswa::where('nim', $nim)->first();

        // Email kosong: pakai akun lama milik NIM ini bila ada, selain itu email bawaan kampus
        $email = !empty($row['email'])
            ? trim($row['email'])
            : (optional(optional($mahasiswaLama)->user)->email ?? $nim . '@student.sttgpipapua.ac.id');

        // Akun staf/dosen/admin tidak boleh ditimpa oleh baris impor mahasiswa yang emailnya bentrok
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && !$this->akunBolehDipakai($existingUser, ['mahasiswa'])) {
            $this->gagal("email {$email} sudah dipakai akun non-mahasiswa.");
        }
        if ($existingUser && $existingUser->mahasiswa && $existingUser->mahasiswa->nim !== $nim) {
            $this->gagal("email {$email} sudah dipakai mahasiswa lain (NIM {$existingUser->mahasiswa->nim}).");
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

        if ($role) {
            $user->assignRole($role->name);
        }

        $teks = fn (string $kolom) => isset($row[$kolom]) && trim((string) $row[$kolom]) !== '' ? ltrim(trim((string) $row[$kolom]), "'") : null;

        // Seluruh kolom template (termasuk data Feeder: alamat rinci, orang tua, wali) ikut tersimpan
        $data = [
            'user_id'           => $user->id,
            'nama_lengkap'      => trim((string) $row['nama_lengkap']),
            'program_studi_id'  => $prodiId,
            'dosen_wali_id'     => $dosenWaliId,
            'tahun_masuk'       => $teks('angkatan') ?? $teks('tahun_masuk'),
            'status_mahasiswa'  => !empty($row['status_mahasiswa']) ? $statusMahasiswa : null,
            'nik'               => $teks('nik'),
            'nisn'              => $teks('nisn'),
            'jalur_pendaftaran' => $teks('jalur_pendaftaran'),
            'tempat_lahir'      => $teks('tempat_lahir'),
            'tanggal_lahir'     => $tanggalLahir,
            'jenis_kelamin'     => $teks('jenis_kelamin') ? strtoupper($teks('jenis_kelamin')) : null,
            'agama'             => $teks('agama'),
            'nomor_telepon'     => $teks('no_hp') ?? $teks('nomor_telepon'),
            'alamat'            => $teks('alamat'),
            'dusun'             => $teks('dusun'),
            'rt'                => $teks('rt'),
            'rw'                => $teks('rw'),
            'kelurahan'         => $teks('kelurahan'),
            'kecamatan'         => $teks('kecamatan'),
            'kode_pos'          => $teks('kode_pos'),
            'jenis_tinggal'     => $teks('jenis_tinggal'),
            'alat_transportasi' => $teks('alat_transportasi'),
            'nama_ibu_kandung'  => $teks('nama_ibu_kandung'),
            'nik_ibu'           => $teks('nik_ibu'),
            'pendidikan_ibu'    => $teks('pendidikan_ibu'),
            'pekerjaan_ibu'     => $teks('pekerjaan_ibu'),
            'penghasilan_ibu'   => $teks('penghasilan_ibu'),
            'nama_ayah'         => $teks('nama_ayah'),
            'nik_ayah'          => $teks('nik_ayah'),
            'pendidikan_ayah'   => $teks('pendidikan_ayah'),
            'pekerjaan_ayah'    => $teks('pekerjaan_ayah'),
            'penghasilan_ayah'  => $teks('penghasilan_ayah'),
            'nama_wali'         => $teks('nama_wali'),
            'pekerjaan_wali'    => $teks('pekerjaan_wali'),
        ];

        if ($mahasiswaLama) {
            // Impor ulang memperbarui data; sel kosong TIDAK menghapus data yang sudah ada
            $mahasiswaLama->update(array_filter($data, fn ($v) => $v !== null));
            return $mahasiswaLama;
        }

        return Mahasiswa::create(array_merge($data, [
            'nim'               => $nim,
            'tahun_masuk'       => $data['tahun_masuk'] ?? date('Y'),
            'status_mahasiswa'  => $statusMahasiswa,
            'jalur_pendaftaran' => $data['jalur_pendaftaran'] ?? 'Mandiri',
            'jenis_kelamin'     => $data['jenis_kelamin'] ?? 'L',
            'agama'             => $data['agama'] ?? 'Kristen Protestan',
        ]));
    }

    public function rules(): array
    {
        return [
            'nim' => 'required',
            'nama_lengkap' => 'required',
        ];
    }
}