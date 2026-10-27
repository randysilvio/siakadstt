<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;

use App\Models\TahunAkademik;
use App\Models\VerumKelas;
use App\Models\VerumPostingan;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class VerumController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:dosen')->only(['create', 'store', 'startMeeting', 'stopMeeting', 'destroy']);
    }
    /**
     * Helper untuk mengecek apakah mahasiswa resmi terdaftar di kelas pada tahun akademik kelas tersebut.
     */
    public function index(): View
    {
        $user = Auth::user();
        
        if ($user->hasRole('admin') && !$user->hasRole('dosen') && !$user->hasRole('mahasiswa')) {
            abort(403, 'Akses ditolak. Administrator tidak memiliki akses ke E-Learning.');
        }

        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->first();
        $semuaKelas = collect();

        if (!$tahunAkademikAktif) {
            return view('verum.index', ['semuaKelas' => $semuaKelas])
                   ->with('error', 'Saat ini tidak ada Tahun Akademik yang aktif.');
        }

        if ($user->hasRole('dosen') && $user->dosen) {
            $pivotMkIds = DB::table('dosen_mata_kuliah')
                ->where('dosen_id', $user->dosen->id)
                ->pluck('mata_kuliah_id')
                ->toArray();

            $semuaKelas = VerumKelas::where('tahun_akademik_id', $tahunAkademikAktif->id)
                                ->where(function ($q) use ($user, $pivotMkIds) {
                                    $q->where('dosen_id', $user->dosen->id)
                                      ->orWhereIn('mata_kuliah_id', $pivotMkIds);
                                })
                                ->with('mataKuliah')
                                ->get();
        } elseif ($user->hasRole('mahasiswa') && $user->mahasiswa) {
            if ($user->mahasiswa->krsDisetujuiPada($tahunAkademikAktif->id)) {
                $rombelPerMk = $user->mahasiswa->mataKuliahs()
                    ->wherePivot('tahun_akademik_id', $tahunAkademikAktif->id)
                    ->get()
                    ->mapWithKeys(fn ($mk) => [$mk->id => strtoupper(trim((string) ($mk->pivot->nama_kelas ?? 'A')))]);

                $semuaKelas = VerumKelas::whereIn('mata_kuliah_id', $rombelPerMk->keys())
                                    ->where('tahun_akademik_id', $tahunAkademikAktif->id)
                                    ->with(['mataKuliah', 'dosen.user'])
                                    ->get()
                                    ->filter(fn ($k) => $k->rombel === null || trim($k->rombel) === ''
                                        || strtoupper(trim($k->rombel)) === ($rombelPerMk[$k->mata_kuliah_id] ?? null))
                                    ->values();
            }
        } else {
            abort(403, 'Data profil akademik tidak ditemukan.');
        }

        return view('verum.index', compact('semuaKelas'));
    }

    public function show(VerumKelas $verum_kela): View
    {
        $user = Auth::user();

        if ($user->hasRole('admin') && !$user->hasRole('dosen')) {
            abort(403, 'Akses ditolak.');
        }

        $isDosenPemilik = Gate::forUser($user)->allows('kelola', $verum_kela);
        $isMahasiswaTerdaftar = Gate::forUser($user)->allows('ikuti', $verum_kela);

        if (!$isDosenPemilik && !$isMahasiswaTerdaftar) {
            abort(403, 'Anda tidak terdaftar di kelas ini atau KRS Anda belum disetujui.');
        }

        $verum_kela->load(['materi', 'tugas', 'postingan.user', 'presensi.kehadiran']);
        return view('verum.show', compact('verum_kela'));
    }

    public function create(): View
    {
        $user = Auth::user();
        if (!$user->dosen) {
             abort(404, 'Data dosen tidak ditemukan.');
        }

        $pivotMkIds = DB::table('dosen_mata_kuliah')
            ->where('dosen_id', $user->dosen->id)
            ->pluck('mata_kuliah_id')
            ->toArray();

        $mataKuliahs = MataKuliah::with('jadwals')
            ->where(function ($q) use ($user, $pivotMkIds) {
                $q->where('dosen_id', $user->dosen->id)->orWhereIn('id', $pivotMkIds);
            })
            ->orderBy('semester')
            ->get();

        return view('verum.create', compact('mataKuliahs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
            'nama_kelas' => 'required|string|max:255',
            'rombel' => 'nullable|string|max:30',
            'deskripsi' => 'nullable|string'
        ]);

        $user = Auth::user();
        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->first();

        if (!$tahunAkademikAktif) {
            return back()->with('error', 'Tidak ada tahun akademik aktif.');
        }

        if (!Gate::forUser($user)->allows('ampu', MataKuliah::findOrFail($request->mata_kuliah_id))) {
            return back()->with('error', 'Anda tidak mengampu mata kuliah ini.');
        }

        $rombel = $request->filled('rombel') ? strtoupper(trim($request->rombel)) : null;
        if ($rombel !== null) {
            $rombelValid = \App\Models\Jadwal::where('mata_kuliah_id', $request->mata_kuliah_id)
                ->whereRaw('UPPER(nama_kelas) = ?', [$rombel])
                ->exists();
            if (!$rombelValid) {
                return back()->withInput()->with('error', "Rombel {$rombel} tidak terdaftar pada jadwal mata kuliah ini.");
            }
        }

        VerumKelas::create([
            'mata_kuliah_id' => $request->mata_kuliah_id,
            'rombel' => $rombel,
            'nama_kelas' => strip_tags($request->nama_kelas),
            'deskripsi' => strip_tags($request->deskripsi),
            'dosen_id' => $user->dosen->id,
            'tahun_akademik_id' => $tahunAkademikAktif->id,
            'kode_kelas' => Str::upper(Str::random(6)),
        ]);

        return redirect()->route('verum.index')->with('success', 'Kelas Verum berhasil dibuat.');
    }

    public function destroy(VerumKelas $verum_kela): RedirectResponse
    {
        if (!Gate::forUser(Auth::user())->allows('kelola', $verum_kela)) {
            abort(403, 'Anda tidak berhak menghapus kelas ini.');
        }

        $verum_kela->delete();
        return redirect()->route('verum.index')->with('success', 'Kelas berhasil dihapus.');
    }

    public function storePost(Request $request, VerumKelas $verum_kela): RedirectResponse
    {
        $user = Auth::user();

        // [PERBAIKAN KEAMANAN FASE 5]: Cegah IDOR! Hanya dosen kelas atau mahasiswa peserta kelas yang boleh posting di forum
        if (!Gate::forUser($user)->allows('kelola', $verum_kela) && !Gate::forUser($user)->allows('ikuti', $verum_kela)) {
            abort(403, 'Akses ditolak. Anda bukan anggota kelas ini.');
        }

        $request->validate(['konten' => 'required|string|max:2000']);

        VerumPostingan::create([
            'kelas_id' => $verum_kela->id,
            'user_id' => $user->id,
            'konten' => strip_tags($request->konten)
        ]);

        return back()->with('success', 'Postingan berhasil ditambahkan.');
    }

    public function destroyPost(VerumPostingan $postingan): RedirectResponse
    {
        $user = Auth::user();
        $isPemilikPost = ((int) $postingan->user_id === (int) $user->id);
        $isDosenKelas = $postingan->kelas && Gate::forUser($user)->allows('kelola', $postingan->kelas);

        if (!$isPemilikPost && !$isDosenKelas) {
            abort(403, 'Anda tidak berhak menghapus postingan ini.');
        }

        $postingan->delete();
        return back()->with('success', 'Postingan berhasil dihapus.');
    }

    public function startMeeting(VerumKelas $verum_kela): RedirectResponse
    {
        if (Gate::forUser(Auth::user())->allows('kelola', $verum_kela)) {
            $verum_kela->update(['is_meeting_active' => true]);
            return back()->with('success', 'Ruang kelas online telah dibuka.');
        }
        return back()->with('error', 'Anda tidak memiliki akses.');
    }

    public function stopMeeting(VerumKelas $verum_kela): RedirectResponse
    {
        if (Gate::forUser(Auth::user())->allows('kelola', $verum_kela)) {
            $verum_kela->update(['is_meeting_active' => false]);
            return back()->with('success', 'Kelas online telah diakhiri.');
        }
        return back()->with('error', 'Anda tidak memiliki akses.');
    }
}