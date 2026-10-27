<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PmbExport;
use App\Http\Controllers\Controller;
use App\Models\Camaba;
use App\Models\Pembayaran;
use App\Models\PmbPeriod;
use App\Models\ProgramStudi;
use App\Services\PmbService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Pengelolaan pendaftar PMB oleh Admin dan Panitia PMB (panitia dibatasi pada tahun kepanitiaannya).
 */
class PmbAdminController extends Controller
{
    public function __construct(private PmbService $pmb)
    {
    }

    private const FILTER = ['search', 'status', 'pmb_period_id', 'pilihan_prodi_1_id'];

    public function index(Request $request)
    {
        $user = Auth::user();
        $pendaftars = $this->pmb->queryPendaftar($user, $request->only(self::FILTER))->paginate(15)->withQueryString();

        $tahun = $this->pmb->tahunDiizinkan($user);
        $periods = PmbPeriod::when($tahun !== null, fn ($q) => $q->whereIn('tahun_pmb', $tahun))->orderBy('id', 'desc')->get();
        $prodis = ProgramStudi::orderBy('nama_prodi')->get();

        return view('admin.pmb.index', compact('pendaftars', 'periods', 'prodis'));
    }

    public function show(Camaba $camaba)
    {
        $camaba->load(['user', 'prodi1', 'prodi2', 'period', 'documents', 'pemutus']);
        $this->pmb->pastikanAkses(Auth::user(), $camaba);
        $tagihan = $this->pmb->tagihanFormulir($camaba);

        return view('admin.pmb.show', compact('camaba', 'tagihan'));
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(self::FILTER);
        $filters['tahun_pmb'] = $this->pmb->tahunDiizinkan(Auth::user());

        return Excel::download(new PmbExport($filters), 'Laporan_Pendaftar_PMB.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $pendaftars = $this->pmb->queryPendaftar(Auth::user(), $request->only(self::FILTER))
            ->reorder('created_at', 'desc')
            ->get();

        return view('admin.pmb.cetak_pdf', compact('pendaftars'));
    }

    /** TAHAP 1: Verifikasi Pembayaran Formulir */
    public function approvePayment($id)
    {
        $pembayaran = Pembayaran::where('jenis_pembayaran', 'formulir_pmb')->findOrFail($id);
        $camaba = Camaba::with('period')->where('user_id', $pembayaran->user_id)->firstOrFail();
        $this->pmb->pastikanAkses(Auth::user(), $camaba);

        try {
            $this->pmb->verifikasiPembayaran($pembayaran, Auth::user());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pembayaran divalidasi LUNAS. Camaba sekarang bisa mengisi biodata.');
    }

    /** TAHAP 2: Finalisasi Kelulusan (Konversi ke Mahasiswa) */
    public function approve(Camaba $camaba)
    {
        $camaba->load(['user', 'prodi1', 'period']);
        $this->pmb->pastikanAkses(Auth::user(), $camaba);

        try {
            $nim = $this->pmb->luluskan($camaba, Auth::user());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Gagal Approve PMB: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses kelulusan. Detail kesalahan telah dicatat di log sistem.');
        }

        return redirect()->route('admin.pmb.index')
            ->with('success', 'Berhasil! ' . $camaba->user->name . ' resmi menjadi Mahasiswa Aktif dengan NIM: ' . $nim);
    }

    public function reject(Request $request, Camaba $camaba)
    {
        $request->validate(['alasan' => 'nullable|string|max:1000']);
        $camaba->load(['user', 'period']);
        $this->pmb->pastikanAkses(Auth::user(), $camaba);

        try {
            $this->pmb->tolak($camaba, $request->input('alasan'), Auth::user());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pendaftaran kandidat telah ditolak.');
    }

    /** Penghapusan pendaftar hanya oleh Admin (rute dibatasi role:admin). */
    public function destroy($id)
    {
        $camaba = Camaba::findOrFail($id);

        try {
            $this->pmb->hapusPendaftar($camaba);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Gagal hapus pendaftar PMB: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus data pendaftar.');
        }

        return back()->with('success', 'Data pendaftar (termasuk dokumen dan tagihan) berhasil dihapus bersih.');
    }
}
