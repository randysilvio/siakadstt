<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;

use App\Support\BerkasPrivat;
use App\Models\BimbinganTugasAkhir;
use App\Models\BimbinganLog;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BimbinganAkademikController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        if (!$user->hasRole('admin') && !$user->hasRole('dosen') && !$user->isKaprodi() && !$user->hasRole('mahasiswa')) {
            abort(403, 'Akses ditolak.');
        }

        $dosens = Dosen::orderBy('nama_lengkap')->get();

        // 1. JIKA MAHASISWA: Tampilkan Bimbingan Miliknya & Form Pengajuan
        if ($user->hasRole('mahasiswa') && $user->mahasiswa) {
            $mahasiswa = $user->mahasiswa;
            $bimbingans = BimbinganTugasAkhir::with(['pembimbing1', 'pembimbing2', 'logs.dosen'])
                ->where('mahasiswa_id', $mahasiswa->id)
                ->latest()
                ->get();

            return view('bimbingan.index', compact('bimbingans', 'dosens', 'mahasiswa'));
        }

        // 2. JIKA DOSEN / KAPRODI / ADMIN: Tampilkan Daftar Bimbingan Mahasiswa
        $query = BimbinganTugasAkhir::with(['mahasiswa.programStudi', 'pembimbing1', 'pembimbing2', 'logs'])->latest();

        if (!$user->hasRole('admin')) {
            if (!$user->dosen) {
                abort(403, 'Profil dosen tidak ditemukan.');
            }
            $dosenId = $user->dosen->id;
            $prodiKaprodiIds = ProgramStudi::where('kaprodi_dosen_id', $dosenId)->pluck('id')->toArray();

            $query->where(function ($q) use ($dosenId, $prodiKaprodiIds) {
                $q->where('pembimbing_1_id', $dosenId)
                  ->orWhere('pembimbing_2_id', $dosenId);

                if (!empty($prodiKaprodiIds)) {
                    $q->orWhereHas('mahasiswa', fn($m) => $m->whereIn('program_studi_id', $prodiKaprodiIds));
                }
            });
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_bimbingan', $request->jenis);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('judul', 'like', "%{$s}%")
                  ->orWhereHas('mahasiswa', fn($m) => $m->where('nama_lengkap', 'like', "%{$s}%")->orWhere('nim', 'like', "%{$s}%"));
            });
        }

        $bimbingans = $query->paginate(12)->withQueryString();

        return view('bimbingan.index', compact('bimbingans', 'dosens'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->mahasiswa) {
            abort(403, 'Hanya mahasiswa yang dapat mengajukan judul bimbingan.');
        }

        $validated = $request->validate([
            'jenis_bimbingan'  => 'required|in:Skripsi / Tugas Akhir,PPL / Vikariat',
            'judul'            => 'required|string|max:350',
            'lokasi_pelayanan' => 'nullable|string|max:255',
            'abstrak_ringkas'  => 'nullable|string|max:2000',
            'pembimbing_1_id'  => 'nullable|exists:dosens,id',
            'pembimbing_2_id'  => 'nullable|exists:dosens,id',
        ]);

        // Satu pengajuan aktif per jenis bimbingan; pengajuan baru hanya setelah yang lama selesai.
        $masihAktif = BimbinganTugasAkhir::where('mahasiswa_id', $user->mahasiswa->id)
            ->where('jenis_bimbingan', $validated['jenis_bimbingan'])
            ->where('status', '!=', 'Selesai / Lulus')
            ->exists();
        if ($masihAktif) {
            return back()->withInput()->with('error', 'Anda masih memiliki pengajuan ' . $validated['jenis_bimbingan'] . ' yang aktif. Ajukan perubahan judul melalui pembimbing atau Kaprodi.');
        }

        BimbinganTugasAkhir::create([
            'mahasiswa_id'     => $user->mahasiswa->id,
            'jenis_bimbingan'  => $validated['jenis_bimbingan'],
            'judul'            => strip_tags($validated['judul']),
            'lokasi_pelayanan' => isset($validated['lokasi_pelayanan']) ? strip_tags($validated['lokasi_pelayanan']) : null,
            'abstrak_ringkas'  => isset($validated['abstrak_ringkas']) ? strip_tags($validated['abstrak_ringkas']) : null,
            'pembimbing_1_id'  => $validated['pembimbing_1_id'] ?? null,
            'pembimbing_2_id'  => $validated['pembimbing_2_id'] ?? null,
            'status'           => 'Diajukan',
        ]);

        return back()->with('success', 'Proposal ' . $validated['jenis_bimbingan'] . ' berhasil diajukan ke Program Studi.');
    }

    public function show(BimbinganTugasAkhir $bimbingan): View
    {
        $user = Auth::user();
        abort_unless(Gate::forUser($user)->allows('lihat', $bimbingan), 403, 'Akses ditolak. Anda tidak memiliki otoritas pada bimbingan ini.');

        $bimbingan->load(['mahasiswa.programStudi', 'pembimbing1', 'pembimbing2', 'logs.dosen']);
        $dosens = Dosen::orderBy('nama_lengkap')->get();

        return view('bimbingan.show', compact('bimbingan', 'dosens'));
    }

    public function updateStatus(Request $request, BimbinganTugasAkhir $bimbingan): RedirectResponse
    {
        $user = Auth::user();
        if ($user->hasRole('mahasiswa')) {
            abort(403);
        }
        abort_unless(Gate::forUser($user)->allows('lihat', $bimbingan), 403, 'Akses ditolak. Anda tidak memiliki otoritas pada bimbingan ini.');

        $validated = $request->validate([
            'judul'           => 'sometimes|required|string|max:350',
            'pembimbing_1_id' => 'nullable|exists:dosens,id',
            'pembimbing_2_id' => 'nullable|exists:dosens,id',
            'status'          => 'required|in:Diajukan,Disetujui / Aktif Bimbingan,Siap Sidang,Selesai / Lulus,Revisi Judul',
            'tanggal_sidang'  => 'nullable|date',
            'ruang_sidang'    => 'nullable|string|max:100',
            'nilai_angka'     => 'nullable|numeric|min:0|max:100',
            'nilai_huruf'     => 'nullable|in:A,B,C,D,E',
            'catatan_kaprodi' => 'nullable|string|max:1000',
        ]);

        // Penetapan pembimbing, jadwal sidang, kelulusan & nilai TA adalah wewenang Kaprodi/Admin.
        // Dosen pembimbing hanya dapat mengubah status proses bimbingan dan judul.
        $isKaprodiAtauAdmin = Gate::forUser($user)->allows('putuskan', $bimbingan);

        if (!$isKaprodiAtauAdmin && $validated['status'] === 'Selesai / Lulus') {
            return back()->with('error', 'Status SELESAI / LULUS hanya dapat ditetapkan oleh Kaprodi atau Admin.');
        }

        $data = [
            'judul'  => isset($validated['judul']) ? strip_tags($validated['judul']) : $bimbingan->judul,
            'status' => $validated['status'],
        ];

        if ($isKaprodiAtauAdmin) {
            $data += [
                'pembimbing_1_id' => $validated['pembimbing_1_id'] ?? $bimbingan->pembimbing_1_id,
                'pembimbing_2_id' => $validated['pembimbing_2_id'] ?? $bimbingan->pembimbing_2_id,
                'tanggal_sidang'  => $validated['tanggal_sidang'] ?? $bimbingan->tanggal_sidang,
                'ruang_sidang'    => isset($validated['ruang_sidang']) ? strip_tags($validated['ruang_sidang']) : $bimbingan->ruang_sidang,
                'nilai_angka'     => $validated['nilai_angka'] ?? $bimbingan->nilai_angka,
                'nilai_huruf'     => $validated['nilai_huruf'] ?? $bimbingan->nilai_huruf,
                // Catatan lama tidak dihapus bila field tidak dikirim
                'catatan_kaprodi' => array_key_exists('catatan_kaprodi', $validated) ? ($validated['catatan_kaprodi'] !== null ? strip_tags($validated['catatan_kaprodi']) : null) : $bimbingan->catatan_kaprodi,
            ];
        }

        $bimbingan->update($data);

        $pesan = $isKaprodiAtauAdmin
            ? 'Status dan parameter bimbingan mahasiswa berhasil diperbarui.'
            : 'Status bimbingan diperbarui. Penetapan pembimbing, sidang, dan nilai dilakukan oleh Kaprodi.';

        return back()->with('success', $pesan);
    }

    public function storeLog(Request $request, BimbinganTugasAkhir $bimbingan): RedirectResponse
    {
        $user = Auth::user();
        abort_unless(Gate::forUser($user)->allows('lihat', $bimbingan), 403, 'Akses ditolak. Anda tidak memiliki otoritas pada bimbingan ini.');

        $validated = $request->validate([
            'tanggal_bimbingan' => 'required|date',
            'dosen_id'          => 'nullable|exists:dosens,id',
            'topik_bab'         => 'required|string|max:200',
            'catatan_mahasiswa' => 'nullable|string|max:2000',
            'file_draf'         => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('file_draf')) {
            $filePath = BerkasPrivat::simpan($request->file('file_draf'), 'draf-bimbingan');
        }

        $bimbingan->logs()->create([
            'tanggal_bimbingan' => $validated['tanggal_bimbingan'],
            'dosen_id'          => $validated['dosen_id'] ?? $bimbingan->pembimbing_1_id,
            'topik_bab'         => strip_tags($validated['topik_bab']),
            'catatan_mahasiswa' => isset($validated['catatan_mahasiswa']) ? strip_tags($validated['catatan_mahasiswa']) : null,
            'file_draf'         => $filePath,
            'status_paraf'      => 'Menunggu Paraf',
        ]);

        return back()->with('success', 'Catatan sesi konsultasi berhasil ditambahkan ke Logbook Bimbingan.');
    }

    public function validasiLog(Request $request, BimbinganLog $log): RedirectResponse
    {
        $user = Auth::user();
        if ($user->hasRole('mahasiswa')) {
            abort(403);
        }
        abort_unless(Gate::forUser($user)->allows('lihat', $log->bimbingan), 403, 'Akses ditolak. Anda tidak memiliki otoritas pada bimbingan ini.');

        $validated = $request->validate([
            'arahan_dosen' => 'nullable|string|max:2000',
            'status_paraf' => 'required|in:Menunggu Paraf,Disetujui / Lanjut,Revisi',
        ]);

        $log->update([
            'arahan_dosen' => isset($validated['arahan_dosen']) ? strip_tags($validated['arahan_dosen']) : null,
            'status_paraf' => $validated['status_paraf'],
            'dosen_id'     => $user->dosen ? $user->dosen->id : $log->dosen_id,
        ]);

        return back()->with('success', 'Paraf dan arahan bimbingan berhasil disimpan.');
    }

    public function cetakKartu(BimbinganTugasAkhir $bimbingan)
    {
        $user = Auth::user();
        abort_unless(Gate::forUser($user)->allows('lihat', $bimbingan), 403, 'Akses ditolak. Anda tidak memiliki otoritas pada bimbingan ini.');

        $bimbingan->load(['mahasiswa.programStudi.kaprodi', 'pembimbing1', 'pembimbing2', 'logs.dosen']);

        $pdf = Pdf::loadView('bimbingan.pdf_kartu', compact('bimbingan'))->setPaper('a4', 'portrait');
        return $pdf->stream('Kartu_Bimbingan_' . $bimbingan->mahasiswa->nim . '.pdf');
    }

}