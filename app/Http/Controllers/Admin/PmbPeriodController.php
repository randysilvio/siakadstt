<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PmbPeriod;
use Illuminate\Http\Request;

class PmbPeriodController extends Controller
{
    public function index()
    {
        $periods = PmbPeriod::orderBy('created_at', 'desc')->get();
        return view('admin.pmb.periods.index', compact('periods'));
    }

    public function create()
    {
        return view('admin.pmb.periods.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_gelombang' => 'required|string|max:255',
            'tanggal_buka' => 'required|date',
            'tanggal_tutup' => 'required|date|after_or_equal:tanggal_buka',
            'biaya_pendaftaran' => 'required|numeric|min:0',
            // [BARU] Validasi Jadwal Ujian
            'tanggal_ujian' => 'nullable|date|after_or_equal:tanggal_tutup',
            'jam_mulai_ujian' => 'nullable',
            'jam_selesai_ujian' => 'nullable|after:jam_mulai_ujian',
            'jenis_ujian' => 'required|in:online,offline',
            'lokasi_ujian' => 'nullable|string',
        ]);

        if ($request->has('is_active')) {
            PmbPeriod::where('is_active', true)->update(['is_active' => false]);
        }

        PmbPeriod::create([
            'nama_gelombang' => $request->nama_gelombang,
            'tanggal_buka' => $request->tanggal_buka,
            'tanggal_tutup' => $request->tanggal_tutup,
            'biaya_pendaftaran' => $request->biaya_pendaftaran,
            'is_active' => $request->has('is_active'),
            // [BARU] Simpan Jadwal
            'tanggal_ujian' => $request->tanggal_ujian,
            'jam_mulai_ujian' => $request->jam_mulai_ujian,
            'jam_selesai_ujian' => $request->jam_selesai_ujian,
            'jenis_ujian' => $request->jenis_ujian,
            'lokasi_ujian' => $request->lokasi_ujian,
        ]);

        return redirect()->route('admin.pmb-periods.index')->with('success', 'Gelombang pendaftaran berhasil dibuat.');
    }

    public function edit(PmbPeriod $pmbPeriod)
    {
        return view('admin.pmb.periods.edit', compact('pmbPeriod'));
    }

    public function update(Request $request, PmbPeriod $pmbPeriod)
    {
        $request->validate([
            'nama_gelombang' => 'required|string|max:255',
            'tanggal_buka' => 'required|date',
            'tanggal_tutup' => 'required|date|after_or_equal:tanggal_buka',
            'biaya_pendaftaran' => 'required|numeric|min:0',
             // [BARU] Validasi Jadwal Ujian
            'tanggal_ujian' => 'nullable|date',
            'jam_mulai_ujian' => 'nullable',
            'jam_selesai_ujian' => 'nullable',
            'jenis_ujian' => 'required|in:online,offline',
            'lokasi_ujian' => 'nullable|string',
        ]);

        if ($request->has('is_active')) {
            PmbPeriod::where('id', '!=', $pmbPeriod->id)->update(['is_active' => false]);
        }

        $pmbPeriod->update([
            'nama_gelombang' => $request->nama_gelombang,
            'tanggal_buka' => $request->tanggal_buka,
            'tanggal_tutup' => $request->tanggal_tutup,
            'biaya_pendaftaran' => $request->biaya_pendaftaran,
            'is_active' => $request->has('is_active'),
            // [BARU] Update Jadwal
            'tanggal_ujian' => $request->tanggal_ujian,
            'jam_mulai_ujian' => $request->jam_mulai_ujian,
            'jam_selesai_ujian' => $request->jam_selesai_ujian,
            'jenis_ujian' => $request->jenis_ujian,
            'lokasi_ujian' => $request->lokasi_ujian,
        ]);

        return redirect()->route('admin.pmb-periods.index')->with('success', 'Gelombang pendaftaran berhasil diperbarui.');
    }

    public function destroy(PmbPeriod $pmbPeriod)
    {
        if ($pmbPeriod->camabas()->exists()) {
            return back()->with('error', 'Tidak bisa menghapus gelombang yang sudah memiliki pendaftar.');
        }
        $pmbPeriod->delete();
        return back()->with('success', 'Gelombang pendaftaran dihapus.');
    }

    public function setActive(PmbPeriod $pmbPeriod)
    {
        PmbPeriod::where('is_active', true)->update(['is_active' => false]);
        $pmbPeriod->update(['is_active' => true]);
        return back()->with('success', 'Gelombang berhasil diaktifkan.');
    }
}