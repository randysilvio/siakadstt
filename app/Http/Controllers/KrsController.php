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
     * Logika pengecekan akses (tagihan & periode aktif) telah dipindahkan ke Middleware
     * untuk mencegah redirect ganda.
     */
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa) {
            abort(403, 'Data mahasiswa tidak ditemukan untuk pengguna ini.');
        }

        // Ambil periode aktif. Gagal jika tidak ada, karena middleware seharusnya sudah memblokir.
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
        
        $max_sks = 15; // Batas SKS default
        if ($ipk >= 3.00) { $max_sks = 24; }
        elseif ($ipk >= 2.50) { $max_sks = 21; }
        elseif ($ipk >= 2.00) { $max_sks = 18; }

        // Ambil ID semua mata kuliah yang sudah LULUS (nilai D ke atas)
        $mk_lulus_ids = $mahasiswa->mataKuliahs()
                                 ->wherePivotIn('nilai', ['A', 'B', 'C', 'D'])
                                 ->pluck('mata_kuliahs.id')->toArray();

        // Ambil semua mata kuliah beserta relasinya
        $mata_kuliahs = MataKuliah::with(['prasyarats', 'jadwals'])->get();
        
        // Ambil KRS untuk periode yang aktif saja
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
     * Metode ini tidak diubah karena logikanya sudah benar.
     */
    public function store(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $mata_kuliah_ids = $request->input('mata_kuliahs', []);
        $periodeAktif = TahunAkademik::where('is_active', true)->firstOrFail();

        // --- VALIDASI SKS DI BACKEND ---
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

        // --- VALIDASI PRASYARAT DI BACKEND ---
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

        // --- VALIDASI JADWAL BENTROK DI BACKEND ---
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

        // UPDATE STATUS KRS MAHASISWA
        $mahasiswa->status_krs = 'Menunggu Persetujuan';
        $mahasiswa->save();

        return redirect()->route('krs.index')->with('success', 'KRS berhasil disimpan dan sedang menunggu persetujuan.');
    }
}
