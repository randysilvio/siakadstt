<?php

namespace App\Http\Controllers;

use App\Support\BerkasPrivat;
use App\Models\Camaba;
use App\Models\ProgramStudi;
use App\Models\PmbDocument;
use App\Models\PmbPeriod;
use App\Models\Pembayaran;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PmbExport;

class PmbController extends Controller
{
    public function index(Request $request)
    {
        $query = Camaba::with(['user', 'prodi1', 'period'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQ) use ($search) {
                    $userQ->where('name', 'like', "%{$search}%");
                })->orWhere('no_pendaftaran', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status_pendaftaran', $request->status);
        }

        if ($request->filled('pmb_period_id')) {
            $query->where('pmb_period_id', $request->pmb_period_id);
        }

        if ($request->filled('pilihan_prodi_1_id')) {
            $query->where('pilihan_prodi_1_id', $request->pilihan_prodi_1_id);
        }

        $pendaftars = $query->paginate(15)->withQueryString();
        $periods = PmbPeriod::orderBy('id', 'desc')->get();
        $prodis = ProgramStudi::orderBy('nama_prodi')->get();

        return view('admin.pmb.index', compact('pendaftars', 'periods', 'prodis'));
    }

    public function show($id)
    {
        $camaba = Camaba::with(['user', 'prodi1', 'prodi2', 'period', 'documents'])->findOrFail($id);
        $tagihan = Pembayaran::where('user_id', $camaba->user_id)->where('jenis_pembayaran', 'formulir_pmb')->latest()->first();
        
        return view('admin.pmb.show', compact('camaba', 'tagihan'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $camaba = Camaba::findOrFail($id);
            $userId = $camaba->user_id;

            foreach ($camaba->documents as $doc) {
                BerkasPrivat::hapus($doc->path_file);
                $doc->delete();
            }

            Pembayaran::where('user_id', $userId)->where('jenis_pembayaran', 'formulir_pmb')->get()
                ->each(function ($p) {
                    BerkasPrivat::hapus($p->bukti_bayar);
                    $p->delete();
                });
            $camaba->delete();

            if ($user = User::find($userId)) {
                $user->delete();
            }

            DB::commit();
            return back()->with('success', 'Data pendaftar (termasuk dokumen dan tagihan) berhasil dihapus bersih.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['status', 'pmb_period_id', 'pilihan_prodi_1_id']);
        return Excel::download(new PmbExport($filters), 'Laporan_Pendaftar_PMB.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $query = Camaba::with(['user', 'prodi1', 'period'])->orderBy('created_at', 'desc');

        if ($request->filled('status')) $query->where('status_pendaftaran', $request->status);
        if ($request->filled('pmb_period_id')) $query->where('pmb_period_id', $request->pmb_period_id);
        if ($request->filled('pilihan_prodi_1_id')) $query->where('pilihan_prodi_1_id', $request->pilihan_prodi_1_id);

        $pendaftars = $query->get();
        return view('admin.pmb.cetak_pdf', compact('pendaftars'));
    }

    public function showPaymentForm()
    {
        $user = Auth::user();
        $tagihan = $user->pembayarans()->where('jenis_pembayaran', 'formulir_pmb')->latest()->first();
        if (!$tagihan) {
            return redirect()->route('dashboard')->with('error', 'Tagihan tidak ditemukan.');
        }
        return view('pmb.pembayaran', compact('tagihan'));
    }

    public function storePaymentProof(Request $request) 
    {
        $request->validate([
            'bukti_bayar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'pembayaran_id' => 'required|exists:pembayarans,id'
        ]);

        $tagihan = Pembayaran::where('id', $request->pembayaran_id)->where('user_id', Auth::id())->firstOrFail();

        if ($tagihan->status === 'lunas') {
            return redirect()->route('dashboard')->with('error', 'Pembayaran Anda sudah diverifikasi lunas.');
        }

        if ($request->hasFile('bukti_bayar')) {
            BerkasPrivat::hapus($tagihan->bukti_bayar);
            $path = BerkasPrivat::simpan($request->file('bukti_bayar'), 'bukti-bayar-pmb');
            $tagihan->update([
                'bukti_bayar' => $path,
                'status' => 'menunggu_konfirmasi',
                'tanggal_bayar' => now()
            ]);
        }
        return redirect()->route('dashboard')->with('success', 'Bukti pembayaran berhasil diunggah! Mohon tunggu verifikasi Admin Keuangan.');
    }

    public function showBiodataForm()
    {
        $user = Auth::user();
        if (!$user->hasRole('camaba') || !$user->camaba) {
            return redirect()->route('dashboard');
        }

        $camaba = $user->camaba;
        $lunas = $user->pembayarans()->where('jenis_pembayaran', 'formulir_pmb')->where('status', 'lunas')->exists();

        if (!$lunas) {
            return redirect()->route('dashboard')->with('error', 'Silakan selesaikan pembayaran formulir terlebih dahulu.');
        }

        $programStudis = ProgramStudi::orderBy('nama_prodi')->get();
        $dokumen = $camaba->documents->pluck('path_file', 'jenis_dokumen');

        return view('pmb.form_biodata', compact('camaba', 'programStudis', 'dokumen'));
    }

    public function updateBiodata(Request $request)
    {
        $user = Auth::user();
        $camaba = $user->camaba;

        if (!$camaba) {
            abort(403, 'Data pendaftar tidak ditemukan.');
        }

        $lunas = $user->pembayarans()->where('jenis_pembayaran', 'formulir_pmb')->where('status', 'lunas')->exists();
        if (!$lunas) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Pembayaran formulir PMB Anda belum diverifikasi lunas.');
        }

        if ($camaba->is_migrated || in_array($camaba->status_pendaftaran, ['lulus', 'tidak_lulus'])) {
            return redirect()->route('dashboard')->with('error', 'Biodata telah dikunci karena proses seleksi Anda sudah selesai.');
        }

        $request->validate([
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string|max:50',
            'alamat' => 'required|string',
            'sekolah_asal' => 'required|string|max:255',
            'nisn' => 'required|numeric|digits_between:8,15',
            'tahun_lulus' => 'required|numeric|digits:4',
            'nilai_rata_rata_rapor' => 'required|numeric|between:0,100',
            'pilihan_prodi_1_id' => 'required|exists:program_studis,id',
            'pilihan_prodi_2_id' => 'nullable|exists:program_studis,id|different:pilihan_prodi_1_id',
            'file_ijazah' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048', 
            'file_kk' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_pas_foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $camaba->update([
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'alamat' => $request->alamat,
                'sekolah_asal' => $request->sekolah_asal,
                'nisn' => $request->nisn,
                'tahun_lulus' => $request->tahun_lulus,
                'nilai_rata_rata_rapor' => $request->nilai_rata_rata_rapor,
                'pilihan_prodi_1_id' => $request->pilihan_prodi_1_id,
                'pilihan_prodi_2_id' => $request->pilihan_prodi_2_id,
                'status_pendaftaran' => 'menunggu_verifikasi',
            ]);

            $this->handleUpload($request, 'file_ijazah', 'Ijazah', $camaba);
            $this->handleUpload($request, 'file_kk', 'Kartu Keluarga', $camaba);
            $this->handleUpload($request, 'file_pas_foto', 'Pas Foto', $camaba);

            DB::commit();
            return redirect()->route('dashboard')->with('success', 'Biodata berhasil disimpan! Anda kini dapat mencetak Kartu Peserta Ujian PMB.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan biodata. Silakan coba lagi.')->withInput();
        }
    }

    public function cetakKartuUjian(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('admin') && $request->filled('camaba_id')) {
            $camaba = Camaba::with(['user', 'prodi1', 'prodi2', 'period', 'documents'])->findOrFail($request->camaba_id);
        } elseif ($user->camaba) {
            $camaba = $user->camaba->load(['user', 'prodi1', 'prodi2', 'period', 'documents']);
        } else {
            abort(403, 'Data kandidat PMB tidak ditemukan.');
        }

        $lunas = Pembayaran::where('user_id', $camaba->user_id)
            ->where('jenis_pembayaran', 'formulir_pmb')
            ->where('status', 'lunas')
            ->exists();

        if (!$lunas) {
            return redirect()->route('dashboard')->with('error', 'Anda wajib menyelesaikan pembayaran formulir terlebih dahulu sebelum mencetak Kartu Peserta Ujian.');
        }

        if (!$camaba->tempat_lahir || !$camaba->pilihan_prodi_1_id) {
            return redirect()->route('pmb.biodata.show')->with('error', 'Silakan lengkapi formulir biodata terlebih dahulu sebelum mencetak Kartu Peserta Ujian.');
        }

        $pasFotoDoc = $camaba->documents->where('jenis_dokumen', 'Pas Foto')->first();
        $pasFotoBase64 = null;
        if ($pasFotoDoc && $pasFotoDoc->path_file) {
            $fullPath = BerkasPrivat::pathAbsolut($pasFotoDoc->path_file);
            if ($fullPath && file_exists($fullPath)) {
                $ext = pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'jpeg';
                $pasFotoBase64 = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($fullPath));
            }
        }

        // Otomatis mendeteksi apakah file view diletakkan di resources/views/pmb/ atau resources/views/admin/pmb/
        $viewName = View::exists('pmb.pdf_kartu_ujian') ? 'pmb.pdf_kartu_ujian' : 'admin.pmb.pdf_kartu_ujian';

        $pdf = Pdf::loadView($viewName, compact('camaba', 'pasFotoBase64'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Kartu_Peserta_Ujian_PMB_' . $camaba->no_pendaftaran . '.pdf');
    }

    private function handleUpload($request, $inputName, $jenisDokumen, $camaba)
    {
        if ($request->hasFile($inputName)) {
            $existingDoc = PmbDocument::where('camaba_id', $camaba->id)->where('jenis_dokumen', $jenisDokumen)->first();
            if ($existingDoc) {
                BerkasPrivat::hapus($existingDoc->path_file);
                $existingDoc->delete();
            }
            $path = BerkasPrivat::simpan($request->file($inputName), 'dokumen-pmb');
            PmbDocument::create([
                'camaba_id' => $camaba->id,
                'jenis_dokumen' => $jenisDokumen,
                'path_file' => $path,
                'status_validasi' => 'pending'
            ]);
        }
    }
}