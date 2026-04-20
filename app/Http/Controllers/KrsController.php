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
     * Menampilkan halaman pengisian KRS (Untuk Website).
     */
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa) {
            abort(403, 'Data mahasiswa tidak ditemukan untuk pengguna ini.');
        }

        $isLocked = ($mahasiswa->status_krs === 'Disetujui');
        $periodeAktif = TahunAkademik::where('is_active', true)->firstOrFail();

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
            'isLocked' => $isLocked 
        ]);
    }

    public function store(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if ($mahasiswa->status_krs === 'Disetujui') {
            return redirect()->route('krs.index')->with('error', 'Gagal menyimpan. KRS Anda sudah final.');
        }

        $mata_kuliah_ids = $request->input('mata_kuliahs', []);
        $periodeAktif = TahunAkademik::where('is_active', true)->firstOrFail();

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

    public function destroy($id)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if ($mahasiswa->status_krs === 'Disetujui') {
            return redirect()->back()->with('error', 'Tidak dapat menghapus. KRS sudah divalidasi oleh Dosen Wali.');
        }
        $mahasiswa->mataKuliahs()->detach($id);
        return redirect()->back()->with('success', 'Mata kuliah berhasil dihapus dari KRS.');
    }

    // =========================================================================
    // FUNGSI API UNTUK MOBILE (DOSEN & MAHASISWA)
    // =========================================================================

    public function getPerluValidasiApi(Request $request)
    {
        $mahasiswa = \App\Models\Mahasiswa::where('status_krs', 'Menunggu Persetujuan')
                        ->select('id', 'name', 'nim', 'status_krs')
                        ->get();

        $formattedData = $mahasiswa->map(function($mhs) {
            return [
                'id' => $mhs->id,
                'mahasiswa' => ['name' => $mhs->name, 'nim' => $mhs->nim]
            ];
        });

        return response()->json($formattedData);
    }

    public function validasiKrsApi(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:Setujui,Tolak']);
        $mahasiswa = \App\Models\Mahasiswa::findOrFail($id);
        
        if ($request->status === 'Setujui') {
            $mahasiswa->status_krs = 'Disetujui';
        } else {
            $mahasiswa->status_krs = 'Ditolak';
        }
        $mahasiswa->save();

        return response()->json(['status' => 'success', 'message' => 'KRS Mahasiswa berhasil ' . $mahasiswa->status_krs]);
    }

    // --- TAMBAHAN BARU: API UNTUK MAHASISWA ISI KRS DI HP ---
    
    public function getFormKrsApi(Request $request)
    {
        $mahasiswa = $request->user()->mahasiswa;
        $periodeAktif = TahunAkademik::where('is_active', true)->first();

        if (!$periodeAktif) return response()->json(['message' => 'Tidak ada periode aktif'], 400);

        // Hitung IPK & Max SKS
        $krs_selesai = $mahasiswa->mataKuliahs()->wherePivotNotNull('nilai')->get();
        $total_sks_lulus = 0; $total_bobot_sks = 0;
        $bobot_nilai = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];

        foreach ($krs_selesai as $mk) {
            $nilai = $mk->pivot->nilai;
            if (isset($bobot_nilai[$nilai])) {
                $total_sks_lulus += $mk->sks;
                $total_bobot_sks += ($bobot_nilai[$nilai] * $mk->sks);
            }
        }

        $ipk = ($total_sks_lulus > 0) ? round($total_bobot_sks / $total_sks_lulus, 2) : 0;
        $max_sks = 15;
        if ($ipk >= 3.00) { $max_sks = 24; }
        elseif ($ipk >= 2.50) { $max_sks = 21; }
        elseif ($ipk >= 2.00) { $max_sks = 18; }

        $allowedSemesters = ($periodeAktif->semester == 'Ganjil') ? [1, 3, 5, 7] : [2, 4, 6, 8];
        
        $mata_kuliahs = MataKuliah::with(['jadwals' => function($q) {
            $q->select('mata_kuliah_id', 'hari', 'jam_mulai', 'jam_selesai');
        }])->whereIn('semester', $allowedSemesters)->get();

        $mk_diambil_ids = $mahasiswa->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $periodeAktif->id)
            ->pluck('mata_kuliahs.id')
            ->toArray();

        return response()->json([
            'ipk' => $ipk,
            'max_sks' => $max_sks,
            'mk_diambil_ids' => $mk_diambil_ids,
            'mata_kuliahs' => $mata_kuliahs,
            'status_krs' => $mahasiswa->status_krs
        ]);
    }

    public function submitKrsApi(Request $request)
    {
        $mahasiswa = $request->user()->mahasiswa;
        
        if ($mahasiswa->status_krs === 'Disetujui') {
            return response()->json(['message' => 'KRS sudah disetujui, tidak bisa diubah.'], 403);
        }

        $mata_kuliah_ids = $request->input('mata_kuliahs', []);
        $periodeAktif = TahunAkademik::where('is_active', true)->first();

        $syncData = [];
        foreach($mata_kuliah_ids as $mk_id){
            $syncData[$mk_id] = ['tahun_akademik_id' => $periodeAktif->id];
        }

        $mahasiswa->mataKuliahs()->sync($syncData);

        if ($mahasiswa->status_krs !== 'Ditolak') {
            $mahasiswa->status_krs = 'Menunggu Persetujuan';
            $mahasiswa->save();
        }

        return response()->json(['message' => 'KRS Berhasil Disimpan']);
    }
}