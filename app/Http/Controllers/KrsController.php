<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
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

        // =====================================================================
        // PERUBAHAN: Hapus Redirect agar mahasiswa tetap bisa LIHAT KRS-nya
        // =====================================================================
        // if ($mahasiswa->status_krs === 'Disetujui') {
        //     return redirect()->route('dashboard')->with('warning', '...');
        // }
        
        // Kita kirim variabel flag agar di View tombol simpan bisa disembunyikan
        $isLocked = ($mahasiswa->status_krs === 'Disetujui' || $mahasiswa->status_krs === 'Menunggu Persetujuan');

        $periodeAktif = TahunAkademik::where('is_active', true)->firstOrFail();

        // Hitung IPK & Max SKS
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

        // Filter Semester (Ganjil/Genap)
        $allowedSemesters = ($periodeAktif->semester == 'Ganjil') ? [1, 3, 5, 7] : [2, 4, 6, 8];

        $mata_kuliahs = MataKuliah::with(['prasyarats', 'jadwals'])
            ->whereIn('semester', $allowedSemesters)
            ->get();

        $mk_diambil_ids = $mahasiswa->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $periodeAktif->id)
            ->pluck('mata_kuliahs.id')
            ->toArray();

        return view('krs.index', [
            'mahasiswa' => $mahasiswa,
            'mata_kuliahs' => $mata_kuliahs,
            'mk_diambil_ids' => $mk_diambil_ids,
            'ipk' => $ipk,
            'max_sks' => $max_sks,
            'mk_lulus_ids' => $mk_lulus_ids,
            'periodeAktif' => $periodeAktif,
            'isLocked' => $isLocked // Kirim status kunci ke view
        ]);
    }

    /**
     * Menyimpan data KRS yang diajukan.
     */
    public function store(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        // PENGAMANAN: Pastikan tetap tidak bisa simpan jika sudah disetujui
        if ($mahasiswa->status_krs === 'Disetujui') {
            return redirect()->route('krs.index')->with('error', 'Gagal menyimpan. KRS Anda sudah final.');
        }

        $mata_kuliah_ids = $request->input('mata_kuliahs', []);
        $periodeAktif = TahunAkademik::where('is_active', true)->firstOrFail();

        // --- Validasi SKS ---
        $krs_selesai = $mahasiswa->mataKuliahs()->wherePivotNotNull('nilai')->get();
        $total_sks_lulus = 0; $total_bobot_sks = 0;
        $bobot_nilai = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];
        foreach ($krs_selesai as $mk) {
            if (isset($bobot_nilai[$mk->pivot->nilai])) {
                $total_sks_lulus += $mk->sks;
                $total_bobot_sks += ($bobot_nilai[$mk->pivot->nilai] * $mk->sks);
            }
        }
        $ipk = ($total_sks_lulus > 0) ? round($total_bobot_sks / $total_sks_lulus, 2) : 0;
        
        $max_sks = 15;
        if ($ipk >= 3.00) $max_sks = 24;
        elseif ($ipk >= 2.50) $max_sks = 21;
        elseif ($ipk >= 2.00) $max_sks = 18;

        $sks_diambil = MataKuliah::whereIn('id', $mata_kuliah_ids)->sum('sks');
        if ($sks_diambil > $max_sks) {
            throw ValidationException::withMessages(['mata_kuliahs' => "SKS berlebih ({$sks_diambil} dari {$max_sks})."]);
        }

        // --- Validasi Prasyarat ---
        $mk_lulus_ids = $mahasiswa->mataKuliahs()->wherePivotIn('nilai', ['A', 'B', 'C', 'D'])->pluck('mata_kuliahs.id')->toArray();
        $mk_dipilih = MataKuliah::with('prasyarats')->findMany($mata_kuliah_ids);
        $error_prasyarat = [];

        foreach ($mk_dipilih as $mk) {
            foreach ($mk->prasyarats as $prasyarat) {
                if (!in_array($prasyarat->id, $mk_lulus_ids)) {
                    $error_prasyarat[] = "Syarat ambil {$mk->nama_mk}: Lulus {$prasyarat->nama_mk}.";
                }
            }
        }

        if (!empty($error_prasyarat)) {
            throw ValidationException::withMessages(['mata_kuliahs' => $error_prasyarat]);
        }

        // --- Validasi Jadwal Bentrok ---
        $jadwalTerpilih = [];
        $mk_dipilih_dengan_jadwal = MataKuliah::with('jadwals')->findMany($mata_kuliah_ids);

        foreach ($mk_dipilih_dengan_jadwal as $mk) {
            foreach ($mk->jadwals as $jadwal) {
                foreach ($jadwalTerpilih as $j) {
                    if ($jadwal->hari == $j['hari'] && $jadwal->jam_mulai < $j['jam_selesai'] && $jadwal->jam_selesai > $j['jam_mulai']) {
                        $mk_bentrok = MataKuliah::find($j['mk_id'])->nama_mk;
                        throw ValidationException::withMessages(['mata_kuliahs' => "Jadwal bentrok: {$mk->nama_mk} vs {$mk_bentrok}."]);
                    }
                }
                $jadwalTerpilih[] = ['hari' => $jadwal->hari, 'jam_mulai' => $jadwal->jam_mulai, 'jam_selesai' => $jadwal->jam_selesai, 'mk_id' => $mk->id];
            }
        }
        
        // --- Simpan Data ---
        $syncData = [];
        foreach($mata_kuliah_ids as $mk_id){
            $syncData[$mk_id] = ['tahun_akademik_id' => $periodeAktif->id];
        }

        $mahasiswa->mataKuliahs()->sync($syncData);

        if ($mahasiswa->status_krs !== 'Ditolak') {
            $mahasiswa->status_krs = 'Menunggu Persetujuan';
            $mahasiswa->save();
        }

        return redirect()->route('krs.index')->with('success', 'KRS berhasil disimpan.');
    }
}