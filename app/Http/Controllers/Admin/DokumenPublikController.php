<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DokumenPublik;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification; 
use App\Notifications\GeneralNotification; 

class DokumenPublikController extends Controller
{
    public function index(Request $request)
    {
        $query = DokumenPublik::latest();
        
        if ($request->filled('search')) {
            $query->where('judul_dokumen', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->input('kategori'));
        }

        $dokumens = $query->paginate(10)->withQueryString();
        $kategoris = DokumenPublik::select('kategori')->distinct()->pluck('kategori');

        return view('admin.dokumen-publik.index', compact('dokumens', 'kategoris'));
    }

    public function create()
    {
        return view('admin.dokumen-publik.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul_dokumen' => 'required|string|max:255',
            'kategori'      => 'required|string|max:100',
            'deskripsi'     => 'nullable|string',
            'file_dokumen'  => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240', 
        ]);

        $filePath = $request->file('file_dokumen')->store('dokumen-publik', 'public');

        DokumenPublik::create([
            'judul_dokumen' => strip_tags($request->judul_dokumen),
            'kategori'      => strip_tags($request->kategori),
            'deskripsi'     => strip_tags($request->deskripsi),
            'file_path'     => $filePath,
        ]);

        $semuaUser = User::all();
        // [PERBAIKAN BUG FASE 6]: Arahkan ke halaman dokumen publik (bukan rute admin)
        Notification::send($semuaUser, new GeneralNotification(
            'Dokumen Publik Baru',
            'Telah diunggah dokumen: ' . strip_tags($request->judul_dokumen),
            route('dokumen.publik'), 
            'bi-file-pdf-fill text-danger'
        ));

        return redirect()->route('admin.dokumen-publik.index')->with('success', 'Dokumen publik berhasil diunggah.');
    }

    public function show(DokumenPublik $dokumen_publik)
    {
        return view('admin.dokumen-publik.show', compact('dokumen_publik'));
    }

    public function destroy(DokumenPublik $dokumen_publik)
    {
        if (Storage::disk('public')->exists($dokumen_publik->file_path)) {
            Storage::disk('public')->delete($dokumen_publik->file_path);
        }
        $dokumen_publik->delete();
        return redirect()->route('admin.dokumen-publik.index')->with('success', 'Dokumen publik berhasil dihapus.');
    }
}