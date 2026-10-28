<?php

namespace Tests\Concerns;

use App\Models\AbsensiPegawai;
use App\Models\BimbinganLog;
use App\Models\BimbinganTugasAkhir;
use App\Models\Camaba;
use App\Models\DokumenPublik;
use App\Models\DokumenTerbit;
use App\Models\Dosen;
use App\Models\DosenTridharma;
use App\Models\EvaluasiJawaban;
use App\Models\EvaluasiPertanyaan;
use App\Models\EvaluasiSesi;
use App\Models\Fakultas;
use App\Models\KegiatanAkademik;
use App\Models\Koleksi;
use App\Models\LokasiKerja;
use App\Models\Mahasiswa;
use App\Models\Pembayaran;
use App\Models\Peminjaman;
use App\Models\PengajuanSurat;
use App\Models\Pengumuman;
use App\Models\PerpustakaanEksemplar;
use App\Models\PmbDocument;
use App\Models\PmbKepanitiaan;
use App\Models\PmbPeriod;
use App\Models\Slideshow;
use App\Models\SuratKeputusan;
use App\Models\Tendik;
use App\Models\TracerStudy;
use App\Models\VerumKehadiran;
use App\Models\VerumKelas;
use App\Models\VerumMateri;
use App\Models\VerumPengumpulan;
use App\Models\VerumPostingan;
use App\Models\VerumPresensi;
use App\Models\VerumTugas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Data lengkap untuk uji asap: setiap tabel berisi minimal satu baris dan setiap peran punya akun,
 * sehingga semua halaman dapat dibuka dengan parameter rute yang nyata.
 */
trait SeedLengkap
{
    use SeedAkademik;

    public function seedLengkap(): array
    {
        $d = $this->seedAkademik();

        return Model::unguarded(function () use ($d) {
            $fak = Fakultas::create(['kode_fakultas' => 'FT', 'nama_fakultas' => 'Fakultas Teologi', 'dekan_dosen_id' => $d['dosen']->id]);
            $d['prodi']->update(['fakultas_id' => $fak->id, 'jenjang' => 'S1']);

            // Kaprodi = dosen lain
            $kUser = $this->buatUser('Kaprodi TH', 'kaprodi@x.test', ['dosen']);
            $kaprodi = Dosen::create(['user_id' => $kUser->id, 'nidn' => '222', 'nama_lengkap' => 'Kaprodi TH', 'jenis_pengajar' => 'Dosen', 'program_studi_id' => $d['prodi']->id]);
            $d['prodi']->update(['kaprodi_dosen_id' => $kaprodi->id]);

            $keuDosenUser = $this->buatUser('Dosen Keu', 'dkeu@x.test', ['dosen']);
            Dosen::create(['user_id' => $keuDosenUser->id, 'nidn' => '444', 'nama_lengkap' => 'Dosen Keu', 'jenis_pengajar' => 'Dosen', 'is_keuangan' => true]);

            $akun = [
                'keuangan'    => $this->buatUser('Staf Keuangan', 'keu@x.test', ['keuangan']),
                'pustakawan'  => $this->buatUser('Pustakawan', 'pus@x.test', ['pustakawan']),
                'rektorat'    => $this->buatUser('Rektor', 'rek@x.test', ['rektorat']),
                'mutu'        => $this->buatUser('LPM', 'lpm@x.test', ['penjaminan_mutu']),
                'adminUmum'   => $this->buatUser('BAU', 'bau@x.test', ['administrasi_umum']),
                'panitia'     => $this->buatUser('Panitia', 'panitia@x.test', ['administrasi_umum']),
                'camabaUser'  => $this->buatUser('Calon', 'calon@x.test', ['camaba']),
            ];
            Tendik::create(['user_id' => $akun['adminUmum']->id, 'nama_lengkap' => 'BAU', 'jenis_tendik' => 'Administrasi', 'jabatan' => 'Staf']);

            // Mahasiswa kedua (untuk uji akses data milik orang lain)
            $m2User = $this->buatUser('Mhs B', 'mhs2@x.test', ['mahasiswa']);
            $mhs2 = Mahasiswa::create(['user_id' => $m2User->id, 'nim' => '2026PK001', 'nama_lengkap' => 'Mhs B', 'program_studi_id' => $d['prodiLain']->id,
                'status_mahasiswa' => 'Aktif', 'status_krs' => 'Belum Mengajukan', 'tahun_masuk' => 2026, 'jenis_kelamin' => 'P', 'nama_ibu_kandung' => 'Ibu B']);

            // KRS & nilai: semester lalu bernilai, semester aktif disetujui
            $taLalu = \App\Models\TahunAkademik::create(['tahun' => '2025/2026', 'semester' => 'Genap', 'is_active' => false,
                'tanggal_mulai_krs' => now()->subYear()->toDateString(), 'tanggal_selesai_krs' => now()->subYear()->addMonth()->toDateString()]);
            $d['mhs']->mataKuliahs()->attach($d['mkLain']->id, ['tahun_akademik_id' => $taLalu->id, 'nilai' => 'B', 'nama_kelas' => 'A',
                'nilai_tugas' => 80, 'nilai_uts' => 75, 'nilai_uas' => 78, 'nilai_kehadiran' => 90, 'nilai_akhir_angka' => 79]);
            $d['mhs']->setStatusKrs($taLalu->id, 'Disetujui', null, $d['dUser']->id);
            $this->ambilDanSetujui($d);
            $mhs2->mataKuliahs()->attach($d['mkLain']->id, ['tahun_akademik_id' => $d['ta']->id, 'nama_kelas' => 'A']);
            $mhs2->setStatusKrs($d['ta']->id, 'Menunggu Persetujuan');

            $spp = Pembayaran::create(['mahasiswa_id' => $d['mhs']->id, 'user_id' => $d['mUser']->id, 'jumlah' => 2500000, 'semester' => $d['ta']->tahun . ' ' . $d['ta']->semester,
                'status' => 'lunas', 'jenis_pembayaran' => 'spp', 'tanggal_bayar' => now()->toDateString(), 'bukti_bayar' => 'private/bukti-bayar-spp/x.jpg']);
            Pembayaran::create(['mahasiswa_id' => $mhs2->id, 'user_id' => $m2User->id, 'jumlah' => 2500000, 'semester' => $d['ta']->tahun . ' ' . $d['ta']->semester,
                'status' => 'menunggu_konfirmasi', 'jenis_pembayaran' => 'spp', 'bukti_bayar' => 'private/bukti-bayar-spp/y.jpg']);

            // PMB
            $periode = PmbPeriod::create(['nama_gelombang' => 'Gelombang I', 'tahun_pmb' => 2027, 'tanggal_buka' => now()->subMonth(), 'tanggal_tutup' => now()->addMonth(),
                'biaya_pendaftaran' => 250000, 'is_active' => true, 'jenis_ujian' => 'offline', 'tanggal_ujian' => now()->addMonths(2)->toDateString(),
                'jam_mulai_ujian' => '08:00', 'jam_selesai_ujian' => '10:00', 'lokasi_ujian' => 'Aula']);
            $camaba = Camaba::create(['user_id' => $akun['camabaUser']->id, 'pmb_period_id' => $periode->id, 'no_pendaftaran' => 'PMB-2027-0001',
                'pilihan_prodi_1_id' => $d['prodi']->id, 'pilihan_prodi_2_id' => $d['prodiLain']->id, 'tempat_lahir' => 'Fakfak', 'tanggal_lahir' => '2007-01-01',
                'jenis_kelamin' => 'L', 'agama' => 'Kristen Protestan', 'no_hp' => '0812', 'alamat' => 'Fakfak', 'sekolah_asal' => 'SMA 1', 'nisn' => '0012345678',
                'tahun_lulus' => 2026, 'nilai_rata_rata_rapor' => 80, 'status_pendaftaran' => 'menunggu_verifikasi']);
            Pembayaran::create(['user_id' => $akun['camabaUser']->id, 'jenis_pembayaran' => 'formulir_pmb', 'jumlah' => 250000, 'status' => 'lunas', 'semester' => 'PMB']);
            $dokPmb = PmbDocument::create(['camaba_id' => $camaba->id, 'jenis_dokumen' => 'IJAZAH', 'path_file' => 'private/pmb/ijazah.pdf', 'status_validasi' => 'pending']);
            PmbKepanitiaan::create(['tahun_pmb' => 2027, 'user_id' => $akun['panitia']->id, 'jabatan' => 'Ketua', 'ditunjuk_oleh' => $d['admin']->id]);

            // Verum
            $kelas = VerumKelas::create(['mata_kuliah_id' => $d['mk']->id, 'dosen_id' => $d['dosen']->id, 'tahun_akademik_id' => $d['ta']->id,
                'nama_kelas' => 'Dogmatika A', 'kode_kelas' => 'VRM-' . Str::upper(Str::random(5)), 'rombel' => 'A']);
            VerumMateri::create(['kelas_id' => $kelas->id, 'judul' => 'Materi 1', 'link_url' => 'https://example.org']);
            $tugas = VerumTugas::create(['kelas_id' => $kelas->id, 'judul' => 'Tugas 1', 'instruksi' => 'Kerjakan', 'tenggat_waktu' => now()->addWeek()]);
            $kumpul = VerumPengumpulan::create(['tugas_id' => $tugas->id, 'mahasiswa_id' => $d['mhs']->id, 'file_path' => 'private/verum/tugas.pdf', 'waktu_pengumpulan' => now(), 'nilai' => 85]);
            VerumPostingan::create(['kelas_id' => $kelas->id, 'user_id' => $d['mUser']->id, 'konten' => 'Halo']);
            $presensi = VerumPresensi::create(['kelas_id' => $kelas->id, 'judul_pertemuan' => 'Pertemuan 1', 'pertemuan_ke' => 1, 'waktu_buka' => now()->subHour(), 'waktu_tutup' => now()->addHour(),
                'materi_aktual' => 'Pengantar', 'metode_pembelajaran' => 'Ceramah']);
            VerumKehadiran::create(['presensi_id' => $presensi->id, 'mahasiswa_id' => $d['mhs']->id, 'status' => 'hadir', 'waktu_absen' => now()]);

            // Evaluasi dosen
            $sesi = EvaluasiSesi::create(['nama_sesi' => 'EDOM Ganjil', 'tahun_akademik_id' => $d['ta']->id, 'tanggal_mulai' => now()->subDay(), 'tanggal_selesai' => now()->addMonth(), 'is_active' => true]);
            $tanya = EvaluasiPertanyaan::create(['pertanyaan' => 'Dosen hadir tepat waktu', 'tipe_jawaban' => 'skala_1_5', 'is_active' => true, 'urutan' => 1]);
            EvaluasiPertanyaan::create(['pertanyaan' => 'Saran', 'tipe_jawaban' => 'teks', 'is_active' => true, 'urutan' => 2]);
            EvaluasiJawaban::create(['evaluasi_sesi_id' => $sesi->id, 'mahasiswa_id' => $mhs2->id, 'dosen_id' => $d['dosen']->id, 'mata_kuliah_id' => $d['mkLain']->id,
                'evaluasi_pertanyaan_id' => $tanya->id, 'jawaban_skala' => 4]);

            // Perpustakaan
            $koleksi = Koleksi::create(['judul' => 'Dogmatika Kristen', 'pengarang' => 'Berkhof', 'penerbit' => 'BPK', 'tahun_terbit' => 2010, 'isbn' => '9789790000000',
                'jumlah_stok' => 2, 'jumlah_tersedia' => 1, 'lokasi_rak' => 'A1']);
            $eks = PerpustakaanEksemplar::create(['koleksi_id' => $koleksi->id, 'kode_barcode' => 'BK-0001', 'status_fisik' => 'Dipinjam']);
            PerpustakaanEksemplar::create(['koleksi_id' => $koleksi->id, 'kode_barcode' => 'BK-0002', 'status_fisik' => 'Tersedia']);
            Peminjaman::create(['koleksi_id' => $koleksi->id, 'eksemplar_id' => $eks->id, 'user_id' => $d['mUser']->id, 'tanggal_pinjam' => now()->subDays(3)->toDateString(),
                'jatuh_tempo' => now()->addDays(4)->toDateString(), 'status' => 'Dipinjam']);

            // Bimbingan TA
            $bimbingan = BimbinganTugasAkhir::create(['mahasiswa_id' => $d['mhs']->id, 'jenis_bimbingan' => 'Skripsi / Tugas Akhir', 'judul' => 'Teologi Kontekstual Papua',
                'pembimbing_1_id' => $d['dosen']->id, 'status' => 'Disetujui / Aktif Bimbingan']);
            $log = BimbinganLog::create(['bimbingan_id' => $bimbingan->id, 'dosen_id' => $d['dosen']->id, 'tanggal_bimbingan' => now()->toDateString(), 'topik_bab' => 'Bab I',
                'catatan_mahasiswa' => 'Draf', 'file_draf' => 'private/draf/bab1.pdf', 'status_paraf' => 'Menunggu Paraf']);

            // Layanan surat & dokumen
            $surat = PengajuanSurat::create(['mahasiswa_id' => $d['mhs']->id, 'jenis_surat' => 'Surat Keterangan Aktif Kuliah', 'keperluan' => 'Beasiswa',
                'status' => 'Disetujui', 'kode_verifikasi' => Str::upper(Str::random(12)), 'nomor_surat' => '001/STT/2026', 'tanggal_disetujui' => now()]);
            $sk = SuratKeputusan::create(['jenis_surat' => 'SK Panitia', 'nomor_surat' => '01/SK/2026', 'judul' => 'Panitia Wisuda', 'tanggal_terbit' => now()->toDateString(),
                'menimbang' => '<p>a</p>', 'mengingat' => '<p>b</p>', 'menetapkan' => '<p>c</p>', 'penandatangan_jabatan' => 'Ketua', 'penandatangan_nama' => 'Pdt. X', 'status' => 'Draf']);
            $sk->dosens()->attach($d['dosen']->id, ['jabatan_dalam_surat' => 'Ketua']);
            $terbit = DokumenTerbit::create(['kode' => 'KHS-' . Str::upper(Str::random(10)), 'jenis' => 'KHS', 'mahasiswa_id' => $d['mhs']->id,
                'ringkasan' => json_encode(['ips' => 3.0]), 'sidik' => hash('sha256', 'x'), 'diterbitkan_oleh' => $d['mUser']->id]);

            // Konten publik & lain-lain
            $berita = Pengumuman::create(['judul' => 'Penerimaan Mahasiswa Baru', 'konten' => '<p>Dibuka</p>', 'target_role' => 'semua', 'kategori' => 'berita', 'slug' => 'penerimaan-mahasiswa-baru']);
            $internal = Pengumuman::create(['judul' => 'Rapat Dosen', 'konten' => '<p>Rapat</p>', 'target_role' => 'dosen', 'kategori' => 'pengumuman', 'slug' => 'rapat-dosen']);
            $slide = Slideshow::create(['judul' => 'Slide', 'gambar' => 'slideshows/a.jpg', 'urutan' => 1, 'is_aktif' => true]);
            $dokPublik = DokumenPublik::create(['judul_dokumen' => 'Pedoman Akademik', 'file_path' => 'dokumen/pedoman.pdf', 'kategori' => 'Pedoman']);
            $kegiatan = KegiatanAkademik::create(['judul_kegiatan' => 'UTS', 'tanggal_mulai' => now()->addMonth()->toDateString(), 'tanggal_selesai' => now()->addMonth()->addWeek()->toDateString(), 'target_role' => 'semua']);
            $lokasi = LokasiKerja::create(['nama_lokasi' => 'Kampus', 'latitude' => -2.9, 'longitude' => 132.3, 'radius_toleransi_meter' => 100]);
            $absen = AbsensiPegawai::create(['user_id' => $d['dUser']->id, 'lokasi_kerja_id' => $lokasi->id, 'tanggal_absensi' => now()->toDateString(),
                'waktu_check_in' => now()->setTime(7, 30), 'foto_check_in' => 'private/absensi/a.jpg', 'status_kehadiran' => 'Hadir']);
            $tridharma = DosenTridharma::create(['dosen_id' => $d['dosen']->id, 'unsur' => 'penelitian', 'kategori' => 'Jurnal', 'judul' => 'Misiologi', 'tahun' => 2026, 'semester' => 'Ganjil', 'sks_bkd' => 1]);
            $mhsAlumni = Mahasiswa::create(['nim' => '2020TH001', 'nama_lengkap' => 'Alumni', 'program_studi_id' => $d['prodi']->id, 'status_mahasiswa' => 'Lulus', 'tahun_masuk' => 2020,
                'jenis_kelamin' => 'L', 'nama_ibu_kandung' => 'Ibu', 'tanggal_lulus' => '2024-08-01']);
            TracerStudy::create(['mahasiswa_id' => $mhsAlumni->id, 'tahun_lulus' => 2024, 'status_pekerjaan' => 'Bekerja', 'waktu_tunggu_bulan' => 3]);
            $d['dUser']->notify(new \App\Notifications\GeneralNotification('Uji', 'Pesan uji', url('/dashboard')));

            return $d + $akun + compact('kUser', 'kaprodi', 'keuDosenUser', 'mhs2', 'm2User', 'taLalu', 'spp', 'periode', 'camaba', 'dokPmb', 'kelas', 'tugas', 'kumpul', 'presensi',
                'sesi', 'tanya', 'koleksi', 'bimbingan', 'log', 'surat', 'sk', 'terbit', 'berita', 'internal', 'slide', 'dokPublik', 'kegiatan', 'lokasi', 'absen', 'tridharma', 'fak', 'mhsAlumni');
        });
    }
}
