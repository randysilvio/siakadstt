<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kurikulum;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class KurikulumController extends Controller
{
    /**
     * Memastikan index unique lama pada nama_kurikulum (global) dilepas
     * agar dua Program Studi berbeda boleh memiliki nama kurikulum yang sama,
     * sementara duplikasi di dalam Prodi yang sama tetap dicegah oleh validasi.
     */
    private function ensureFlexibleUniqueIndex(): void
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `kurikulums` WHERE Key_name = 'kurikulums_nama_kurikulum_unique'");
            if (!empty($indexes)) {
                Schema::table('kurikulums', function ($table) {
                    $table->dropUnique('kurikulums_nama_kurikulum_unique');
                });
            }
        } catch (\Throwable $e) {
            // Abaikan jika index sudah pernah dihapus sebelumnya
        }
    }

    public function index()
    {
        $kurikulums = Kurikulum::with('programStudi')
            ->orderBy('program_studi_id', 'asc')
            ->orderBy('tahun', 'desc')
            ->get();

        return view('admin.kurikulum.index', compact('kurikulums'));
    }

    public function create()
    {
        $program_studis = ProgramStudi::orderBy('nama_prodi')->get();
        return view('admin.kurikulum.create', compact('program_studis'));
    }

    public function store(Request $request)
    {
        $this->ensureFlexibleUniqueIndex();

        $request->merge([
            'nama_kurikulum' => trim((string) $request->input('nama_kurikulum')),
        ]);

        $validated = $request->validate([
            'program_studi_id' => 'required|exists:program_studis,id',
            'nama_kurikulum'   => [
                'required',
                'string',
                'max:255',
                Rule::unique('kurikulums', 'nama_kurikulum')->where(function ($query) use ($request) {
                    return $query->where('program_studi_id', $request->input('program_studi_id'));
                }),
            ],
            'tahun'            => 'required|digits:4|integer|min:2000',
        ], [
            'nama_kurikulum.unique' => 'Nama kurikulum "' . $request->input('nama_kurikulum') . '" sudah terdaftar pada Program Studi tersebut. Silakan gunakan nama atau tahun pembeda.',
        ]);

        $validated['is_active'] = false;

        // Jika prodi ini belum punya kurikulum aktif sama sekali, langsung aktifkan kurikulum pertama ini
        $hasActiveInProdi = Kurikulum::where('program_studi_id', $validated['program_studi_id'])
            ->where('is_active', true)
            ->exists();

        if (!$hasActiveInProdi) {
            $validated['is_active'] = true;
        }

        Kurikulum::create($validated);

        return redirect()->route('admin.kurikulum.index')
            ->with('success', 'Kurikulum berhasil dibuat' . ($validated['is_active'] ? ' dan langsung diaktifkan untuk Program Studi tersebut.' : '.'));
    }

    public function edit(Kurikulum $kurikulum)
    {
        $program_studis = ProgramStudi::orderBy('nama_prodi')->get();
        return view('admin.kurikulum.edit', compact('kurikulum', 'program_studis'));
    }

    public function update(Request $request, Kurikulum $kurikulum)
    {
        $this->ensureFlexibleUniqueIndex();

        $request->merge([
            'nama_kurikulum' => trim((string) $request->input('nama_kurikulum')),
        ]);

        $validated = $request->validate([
            'program_studi_id' => 'required|exists:program_studis,id',
            'nama_kurikulum'   => [
                'required',
                'string',
                'max:255',
                Rule::unique('kurikulums', 'nama_kurikulum')
                    ->where(fn($query) => $query->where('program_studi_id', $request->input('program_studi_id')))
                    ->ignore($kurikulum->id),
            ],
            'tahun'            => 'required|digits:4|integer|min:2000',
        ], [
            'nama_kurikulum.unique' => 'Nama kurikulum "' . $request->input('nama_kurikulum') . '" sudah digunakan pada Program Studi tersebut.',
        ]);

        $kurikulum->update($validated);

        return redirect()->route('admin.kurikulum.index')->with('success', 'Kurikulum berhasil diperbarui.');
    }

    public function destroy(Kurikulum $kurikulum)
    {
        if ($kurikulum->is_active) {
            return back()->with('error', 'Tidak dapat menghapus kurikulum yang sedang berstatus AKTIF.');
        }
        
        if ($kurikulum->mataKuliahs()->exists()) {
            return back()->with('error', 'Kurikulum ini sudah menaungi mata kuliah dan tidak dapat dihapus.');
        }

        $kurikulum->delete();
        return redirect()->route('admin.kurikulum.index')->with('success', 'Kurikulum berhasil dihapus.');
    }

    public function setActive(Kurikulum $kurikulum)
    {
        DB::transaction(function () use ($kurikulum) {
            if ($kurikulum->program_studi_id) {
                Kurikulum::where('program_studi_id', $kurikulum->program_studi_id)
                         ->update(['is_active' => false]);
            } else {
                Kurikulum::query()->update(['is_active' => false]);
            }
            
            $kurikulum->update(['is_active' => true]);
        });

        return redirect()->route('admin.kurikulum.index')
            ->with('success', "Kurikulum {$kurikulum->nama_kurikulum} berhasil diaktifkan.");
    }
}