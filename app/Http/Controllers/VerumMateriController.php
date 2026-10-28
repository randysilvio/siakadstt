<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;

use App\Models\VerumKelas;
use App\Models\VerumMateri;
use App\Services\VerumService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class VerumMateriController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:dosen']);
    }
    /**
     * Menyimpan materi baru ke database dengan sanitasi input.
     */
    public function store(Request $request, VerumKelas $verum_kela): RedirectResponse
    {
        if (!Gate::forUser(Auth::user())->allows('kelola', $verum_kela)) {
            abort(403, 'Aksi ini hanya untuk dosen pengampu kelas.');
        }

        $data = $request->validate(VerumService::ATURAN_MATERI, VerumService::PESAN_MATERI);

        $hasil = app(VerumService::class)->simpanMateri($verum_kela, $data, $request->file('file_materi'));
        if (is_string($hasil)) {
            return back()->withErrors(['file_materi' => $hasil])->withInput();
        }

        return back()->with('success', 'Materi berhasil ditambahkan.');
    }

    /**
     * Menghapus materi secara aman.
     */
    public function destroy(VerumMateri $verum_materi): RedirectResponse
    {
        if (!$verum_materi->kelas || !Gate::forUser(Auth::user())->allows('kelola', $verum_materi->kelas)) {
            abort(403, 'Anda tidak berhak menghapus materi ini.');
        }

        app(VerumService::class)->hapusMateri($verum_materi);

        return back()->with('success', 'Materi berhasil dihapus.');
    }
}