<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class KrsController extends Controller
{
    /**
     * Menampilkan halaman pengisian KRS.
     */
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa) {
            abort(403, 'Data mahasiswa tidak ditemukan untuk pengguna ini.');
        }

        // --- PERBAIKAN 1: Tambahkan pengecekan status di Controller ---
        // Lapisan pengaman kedua jika middleware gagal atau tidak diterapkan.
        if ($mahasiswa->status_krs === 'Disetujui') {
            return redirect()->route('dashboard')->with('warning', 'KRS Anda telah disetujui dan tidak dapat diubah lagi.');
        }

        $periodeAktif = TahunAkademik::where('is_active', true)->firstOrFail();

        // Logika untuk menghitung IPK dan menentukan batas SKS
        $krs_selesai = $mahasiswa->mataKuliahs()->wherePivotNotNull('nilai')->get();
        $total_sks_lulus = 0;
        $total_bobot_sks = 0;
        $bobot_nilai = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];

        foreach ($krs_selesai as $mk) {
            $sks = $mk->sks;
            $nilai = $mk->pivot->nilai;
            if (isset($bobot_nilai[$nilai])) {
                $total_sks_lulus += $sks;
                $total_bobot_sks += ($bobot_nilai[$nilai] * $sks);
            }
        }

        $ipk = ($total_sks_lulus > 0) ? round($total_bobot_sks / $total_sks_lulus, 2) : 0;
        
        $max_sks = 15;
        if ($ipk >= 3.00) { $max_sks = 24; }
        elseif ($ipk >= 2.50) { $max_sks = 21; }
        elseif ($ipk >= 2.00) { $max_sks = 18; }

        $mk_lulus_ids = $mahasiswa->mataKuliahs()
                                 ->wherePivotIn('nilai', ['A', 'B', 'C', 'D'])
                                 ->pluck('mata_kuliahs.id')->toArray();

        $mata_kuliahs = MataKuliah::with(['prasyarats', 'jadwals'])->get();
        
        $mk_diambil_ids = $mahasiswa->mataKuliahs()->where('tahun_akademik_id', $periodeAktif->id)->pluck('mata_kuliahs.id')->toArray();

        return view('krs.index', [
            'mahasiswa' => $mahasiswa,
            'mata_kuliahs' => $mata_kuliahs,
            'mk_diambil_ids' => $mk_diambil_ids,
            'ipk' => $ipk,
            'max_sks' => $max_sks,
            'mk_lulus_ids' => $mk_lulus_ids,
        ]);
    }

    /**
     * Menyimpan data KRS yang diajukan.
     */
    public function store(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        // --- PERBAIKAN 2: Tambahkan pengecekan status sebelum menyimpan ---
        // Mencegah mahasiswa mengirim ulang data jika KRS sudah disetujui.
        if ($mahasiswa->status_krs === 'Disetujui') {
            return redirect()->route('krs.index')->with('error', 'Gagal menyimpan. KRS Anda sudah final dan tidak dapat diubah.');
        }

        $mata_kuliah_ids = $request->input('mata_kuliahs', []);
        $periodeAktif = TahunAkademik::where('is_active', true)->firstOrFail();

        // Validasi SKS, Prasyarat, dan Jadwal Bentrok
        $krs_selesai = $mahasiswa->mataKuliahs()->wherePivotNotNull('nilai')->get();
        $total_sks_lulus = 0;
        $total_bobot_sks = 0;
        $bobot_nilai = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];
        foreach ($krs_selesai as $mk) {
            $sks = $mk->sks;
            $nilai = $mk->pivot->nilai;
            if (isset($bobot_nilai[$nilai])) {
                $total_sks_lulus += $sks;
                $total_bobot_sks += ($bobot_nilai[$nilai] * $sks);
            }
        }
        $ipk = ($total_sks_lulus > 0) ? round($total_bobot_sks / $total_sks_lulus, 2) : 0;
        
        $max_sks = 15;
        if ($ipk >= 3.00) { $max_sks = 24; }
        elseif ($ipk >= 2.50) { $max_sks = 21; }
        elseif ($ipk >= 2.00) { $max_sks = 18; }

        $sks_diambil = MataKuliah::whereIn('id', $mata_kuliah_ids)->sum('sks');
        if ($sks_diambil > $max_sks) {
            throw ValidationException::withMessages([
                'mata_kuliahs' => "Total SKS yang Anda ambil ({$sks_diambil} SKS) melebihi batas maksimum ({$max_sks} SKS).",
            ]);
        }

        $mk_lulus_ids = $mahasiswa->mataKuliahs()->wherePivotIn('nilai', ['A', 'B', 'C', 'D'])->pluck('mata_kuliahs.id')->toArray();
        $mk_dipilih = MataKuliah::with('prasyarats')->findMany($mata_kuliah_ids);
        $error_prasyarat = [];

        foreach ($mk_dipilih as $mk) {
            foreach ($mk->prasyarats as $prasyarat) {
                if (!in_array($prasyarat->id, $mk_lulus_ids)) {
                    $error_prasyarat[] = "Untuk mengambil {$mk->nama_mk}, Anda harus lulus {$prasyarat->nama_mk} terlebih dahulu.";
                }
            }
        }

        if (!empty($error_prasyarat)) {
            throw ValidationException::withMessages(['mata_kuliahs' => $error_prasyarat]);
        }

        $jadwalTerpilih = [];
        $mk_dipilih_dengan_jadwal = MataKuliah::with('jadwals')->findMany($mata_kuliah_ids);

        foreach ($mk_dipilih_dengan_jadwal as $mk) {
            foreach ($mk->jadwals as $jadwal) {
                foreach ($jadwalTerpilih as $j) {
                    if ($jadwal->hari == $j['hari'] && $jadwal->jam_mulai < $j['jam_selesai'] && $jadwal->jam_selesai > $j['jam_mulai']) {
                        $nama_mk_bentrok = MataKuliah::find($j['mk_id'])->nama_mk;
                        throw ValidationException::withMessages([
                            'mata_kuliahs' => "Jadwal bentrok antara {$mk->nama_mk} dengan {$nama_mk_bentrok}.",
                        ]);
                    }
                }
                $jadwalTerpilih[] = ['hari' => $jadwal->hari, 'jam_mulai' => $jadwal->jam_mulai, 'jam_selesai' => $jadwal->jam_selesai, 'mk_id' => $mk->id];
            }
        }
        
        // Menyiapkan data untuk di-sync ke tabel pivot
        $syncData = [];
        foreach($mata_kuliah_ids as $mk_id){
            $syncData[$mk_id] = ['tahun_akademik_id' => $periodeAktif->id];
        }

        $mahasiswa->mataKuliahs()->sync($syncData);

        // --- PERBAIKAN 3: Ubah status hanya jika belum pernah disetujui/ditolak ---
        if ($mahasiswa->status_krs !== 'Ditolak') {
            $mahasiswa->status_krs = 'Menunggu Persetujuan';
            $mahasiswa->save();
        }

        return redirect()->route('krs.index')->with('success', 'KRS berhasil disimpan dan sedang menunggu persetujuan.');
    }
}
