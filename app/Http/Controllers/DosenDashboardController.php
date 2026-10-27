<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf; 
use App\Models\ProgramStudi;
use App\Models\MataKuliah;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Models\Jadwal; 
use App\Models\Pengumuman;

class DosenDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:dosen');
    }
    
    public function index(Request $request)
    {
        $user = Auth::user();
        $dosen = $user->dosen;
        if (!$dosen) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $tahunAkademik = TahunAkademik::where('is_active', true)->first();
        
        $pivotMkIds = DB::table('dosen_mata_kuliah')
            ->where('dosen_id', $dosen->id)
            ->pluck('mata_kuliah_id')
            ->toArray();
        
        $mata_kuliahs = MataKuliah::where(function ($query) use ($dosen, $pivotMkIds) {
                $query->where('dosen_id', $dosen->id)
                      ->orWhereIn('id', $pivotMkIds);
            })
            ->where(function ($q) use ($tahunAkademik) {
                $q->whereHas('kurikulum', fn($k) => $k->where('is_active', true));
                if ($tahunAkademik) {
                    $q->orWhereHas('mahasiswas', fn($m) => $m->where('mahasiswa_mata_kuliah.tahun_akademik_id', $tahunAkademik->id));
                }
            })
            ->with(['mahasiswas' => function ($query) use ($tahunAkademik) {
                $query->where('mahasiswa_mata_kuliah.tahun_akademik_id', $tahunAkademik->id ?? 0)
                      ->orderBy('nim', 'asc');
            }, 'mahasiswas.programStudi', 'kurikulum.programStudi'])
            ->withCount(['mahasiswas' => function ($query) use ($tahunAkademik) {
                $query->where('mahasiswa_mata_kuliah.tahun_akademik_id', $tahunAkademik->id ?? 0);
            }])
            ->orderBy('semester')
            ->orderBy('kode_mk')
            ->get();

        // Hitung progres input nilai per mata kuliah untuk Dosen Command Center
        $totalPesertaSemuaMk = 0;
        $totalSudahDinilaiSemuaMk = 0;
        $mkBelumLengkapNilai = 0;

        foreach ($mata_kuliahs as $mk) {
            $pesertaDisetujui = $mk->mahasiswas->where('status_krs', 'Disetujui');
            $jmlPeserta = $pesertaDisetujui->count();
            $jmlDinilai = $pesertaDisetujui->filter(fn($m) => !empty($m->pivot->nilai))->count();

            $mk->jml_peserta_disetujui = $jmlPeserta;
            $mk->jml_sudah_dinilai = $jmlDinilai;

            $totalPesertaSemuaMk += $jmlPeserta;
            $totalSudahDinilaiSemuaMk += $jmlDinilai;

            if ($jmlPeserta > 0 && $jmlDinilai < $jmlPeserta) {
                $mkBelumLengkapNilai++;
            }
        }

        $persenNilaiTerisi = $totalPesertaSemuaMk > 0
            ? round(($totalSudahDinilaiSemuaMk / $totalPesertaSemuaMk) * 100)
            : 100;
        
        $jadwalKuliah = collect();
        if ($mata_kuliahs->isNotEmpty()) {
            $mkIds = $mata_kuliahs->pluck('id');
            $jadwalKuliah = Jadwal::with('mataKuliah')
                ->whereIn('mata_kuliah_id', $mkIds)
                ->get()
                ->sortBy(function ($jadwal) {
                    $hariOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
                    return $hariOrder[$jadwal->hari] ?? 99;
                });
        }

        // Hanya hitung mahasiswa Tutorship yang berstatus Aktif
        $jumlahMahasiswaWali = $dosen->mahasiswaWali()->where('status_mahasiswa', 'Aktif')->count();
        $krsWaliMenunggu = 0;
        if ($tahunAkademik) {
            $krsWaliMenunggu = $dosen->mahasiswaWali()
                ->where('status_mahasiswa', 'Aktif')
                ->where('status_krs', 'Menunggu Persetujuan')
                ->whereHas('mataKuliahs', fn($q) => $q->where('mahasiswa_mata_kuliah.tahun_akademik_id', $tahunAkademik->id))
                ->count();
        }

        $prodiYangDikepalai = ProgramStudi::where('kaprodi_dosen_id', $dosen->id)->first();
        $dataKaprodi = null;
        if ($prodiYangDikepalai) {
            $krsProdiMenunggu = Mahasiswa::where('program_studi_id', $prodiYangDikepalai->id)
                ->where('status_mahasiswa', 'Aktif')
                ->where('status_krs', 'Menunggu Persetujuan')
                ->when($tahunAkademik, function ($q) use ($tahunAkademik) {
                    $q->whereHas('mataKuliahs', fn($sub) => $sub->where('mahasiswa_mata_kuliah.tahun_akademik_id', $tahunAkademik->id));
                })
                ->count();

            $dataKaprodi = [
                'prodi' => $prodiYangDikepalai,
                'krs_count' => $krsProdiMenunggu,
            ];
        }

        $ringkasanBkd = method_exists($dosen, 'hitungRingkasanBkd') ? $dosen->hitungRingkasanBkd() : null;

        $pengumumans = Pengumuman::whereIn('target_role', Pengumuman::targetUntukRoles($user->roles->pluck('name')->toArray()))
            ->latest()->take(5)->get();

        $query = $dosen->suratKeputusans()->where('status', 'Selesai');
        if ($request->filled('search_surat')) {
            $search = $request->search_surat;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('nomor_surat', 'like', "%{$search}%");
            });
        }
        $suratKeputusans = $query->latest()->paginate(5)->withQueryString();

        return view('dosen.dashboard', compact(
            'dosen',
            'mata_kuliahs',
            'jadwalKuliah',
            'jumlahMahasiswaWali',
            'krsWaliMenunggu',
            'prodiYangDikepalai',
            'dataKaprodi',
            'pengumumans',
            'suratKeputusans',
            'tahunAkademik',
            'persenNilaiTerisi',
            'mkBelumLengkapNilai',
            'ringkasanBkd'
        ));
    }

    public function cetakJadwal()
    {
        $dosen = Auth::user()->dosen;
        if (!$dosen) abort(403);
        $tahunAkademik = TahunAkademik::where('is_active', true)->firstOrFail();

        $pivotMkIds = DB::table('dosen_mata_kuliah')
            ->where('dosen_id', $dosen->id)
            ->pluck('mata_kuliah_id')
            ->toArray();
            
        $mkIds = MataKuliah::where(function ($q) use ($dosen, $pivotMkIds) {
                $q->where('dosen_id', $dosen->id)
                  ->orWhereIn('id', $pivotMkIds);
            })
            ->where(function ($q) use ($tahunAkademik) {
                $q->whereHas('kurikulum', fn($k) => $k->where('is_active', true))
                  ->orWhereHas('mahasiswas', fn($m) => $m->where('mahasiswa_mata_kuliah.tahun_akademik_id', $tahunAkademik->id));
            })
            ->pluck('id');

        $jadwals = Jadwal::with(['mataKuliah', 'mataKuliah.kurikulum.programStudi'])
            ->whereIn('mata_kuliah_id', $mkIds)
            ->get()
            ->sortBy(function ($jadwal) {
                // Urut per hari lalu per jam mulai
                $hariOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
                return sprintf('%02d-%s', $hariOrder[$jadwal->hari] ?? 99, $jadwal->jam_mulai);
            });

        $pdf = Pdf::loadView('dosen.cetak_jadwal', compact('dosen', 'jadwals', 'tahunAkademik'));
        return $pdf->stream('Jadwal_Mengajar_' . $dosen->nama_lengkap . '.pdf');
    }

    public function cetakAbsensi(MataKuliah $mataKuliah)
    {
        $dosen = Auth::user()->dosen;
        if (!$dosen) abort(403);
        $tahunAkademik = TahunAkademik::where('is_active', true)->firstOrFail();

        $isTeam = DB::table('dosen_mata_kuliah')
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->where('dosen_id', $dosen->id)
            ->exists();

        if ((int) $mataKuliah->dosen_id !== (int) $dosen->id && !$isTeam) {
            abort(403, 'Akses ditolak. Anda bukan pengajar kelas ini.');
        }

        $mahasiswas = $mataKuliah->mahasiswas()
            ->wherePivot('tahun_akademik_id', $tahunAkademik->id)
            ->orderBy('nim', 'asc')
            ->get();

        $pdf = Pdf::loadView('dosen.pdf_absensi', compact('dosen', 'mataKuliah', 'tahunAkademik', 'mahasiswas'))
               ->setPaper('a4', 'landscape');
               
        return $pdf->stream('Daftar_Hadir_' . str_replace(' ', '_', $mataKuliah->kode_mk) . '.pdf');
    }

    public function uploadRps(Request $request, MataKuliah $mataKuliah)
    {
        $dosen = Auth::user()->dosen;
        if (!$dosen) abort(403);
        
        $isTeam = DB::table('dosen_mata_kuliah')
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->where('dosen_id', $dosen->id)
            ->exists();

        if ((int) $mataKuliah->dosen_id !== (int) $dosen->id && !$isTeam) {
            abort(403, 'Anda tidak berhak mengubah RPS mata kuliah ini.');
        }

        $request->validate([
            'file_rps' => 'required|mimes:pdf|max:5120',
        ], [
            'file_rps.required' => 'Silakan pilih file RPS terlebih dahulu.',
            'file_rps.mimes' => 'Format dokumen RPS wajib berupa file PDF.',
            'file_rps.max' => 'Ukuran file RPS maksimal adalah 5MB.',
        ]);

        if ($request->hasFile('file_rps')) {
            if ($mataKuliah->file_rps && Storage::disk('public')->exists($mataKuliah->file_rps)) {
                Storage::disk('public')->delete($mataKuliah->file_rps);
            }
            $path = $request->file('file_rps')->store('rps', 'public');
            $mataKuliah->update(['file_rps' => $path]);
        }

        return redirect()->back()->with('success', 'Dokumen RPS untuk mata kuliah ' . $mataKuliah->nama_mk . ' berhasil diunggah.');
    }
}