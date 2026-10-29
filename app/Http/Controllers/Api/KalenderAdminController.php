<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\CekPeriodeKrsMiddleware;
use App\Models\KegiatanAkademik;
use App\Models\Pengaturan;
use App\Models\PmbPeriod;
use App\Models\Role;
use App\Models\TahunAkademik;
use App\Services\KalenderPengendali;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Panel kendali Kalender Akademik di aplikasi (khusus Admin).
 * Aturan & efeknya sama persis dengan halaman web (App\Services\KalenderPengendali).
 */
class KalenderAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $jenis = $request->query('jenis');
        $status = $request->query('status');
        $hari = now()->toDateString();

        $daftar = KegiatanAkademik::with(['roles:id,name,display_name', 'tahunAkademik', 'pmbPeriod'])
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('judul_kegiatan', 'like', "%{$q}%")->orWhere('deskripsi', 'like', "%{$q}%")))
            ->when($jenis === 'pengendali', fn ($w) => $w->where('jenis', '!=', 'informasi'))
            ->when($jenis && $jenis !== 'pengendali' && isset(KalenderPengendali::JENIS[$jenis]), fn ($w) => $w->where('jenis', $jenis))
            ->when($status === 'berlangsung', fn ($w) => $w->whereDate('tanggal_mulai', '<=', $hari)->whereDate('tanggal_selesai', '>=', $hari))
            ->when($status === 'akan_datang', fn ($w) => $w->whereDate('tanggal_mulai', '>', $hari))
            ->when($status === 'selesai', fn ($w) => $w->whereDate('tanggal_selesai', '<', $hari))
            ->orderByDesc('tanggal_mulai')
            ->paginate(20);

        return response()->json([
            'data' => $daftar->getCollection()->map(fn ($k) => $this->bentuk($k))->values(),
            'halaman' => $daftar->currentPage(),
            'halaman_terakhir' => $daftar->lastPage(),
            'total' => $daftar->total(),
            'ringkasan' => $this->ringkasan(),
        ]);
    }

    /** Pilihan untuk formulir (jenis, peran, tahun akademik, gelombang PMB). */
    public function opsi(): JsonResponse
    {
        return response()->json([
            'jenis' => collect(KalenderPengendali::JENIS)->map(fn ($label, $kunci) => [
                'kunci' => $kunci, 'label' => $label,
                'butuh' => in_array($kunci, KalenderPengendali::PER_TAHUN_AKADEMIK, true) ? 'tahun_akademik' : ($kunci === 'pmb' ? 'pmb' : null),
            ])->values(),
            'peran' => Role::orderBy('name')->get(['id', 'name', 'display_name'])->map(fn ($r) => ['id' => $r->id, 'nama' => $r->display_name ?: ucfirst($r->name)]),
            'tahun_akademik' => TahunAkademik::orderByDesc('tahun')->orderByDesc('semester')->get()
                ->map(fn ($t) => ['id' => $t->id, 'nama' => $t->nama_semester, 'aktif' => (bool) $t->is_active]),
            'pmb' => PmbPeriod::orderByDesc('tahun_pmb')->orderBy('nama_gelombang')->get()
                ->map(fn ($p) => ['id' => $p->id, 'nama' => trim($p->nama_gelombang . ' ' . $p->tahun_pmb)]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $k = KalenderPengendali::simpan($request->validate(KalenderPengendali::ATURAN));
        return response()->json([
            'message' => 'Agenda ditambahkan.' . (KalenderPengendali::pengendali($k) ? ' Pengaturan sistem terkait sudah diperbarui otomatis.' : ''),
            'data' => $this->bentuk($k),
        ], 201);
    }

    public function update(Request $request, KegiatanAkademik $kegiatan): JsonResponse
    {
        $k = KalenderPengendali::simpan($request->validate(KalenderPengendali::ATURAN), $kegiatan);
        return response()->json(['message' => 'Agenda diperbarui.', 'data' => $this->bentuk($k)]);
    }

    public function destroy(KegiatanAkademik $kegiatan): JsonResponse
    {
        $pengendali = KalenderPengendali::pengendali($kegiatan);
        KalenderPengendali::hapus($kegiatan);
        return response()->json(['message' => 'Agenda dihapus.' . ($pengendali ? ' Tanggal terakhir tetap tersimpan dan kini bisa diubah manual di web.' : '')]);
    }

    /** Saklar darurat KRS (menang atas kalender). */
    public function saklarKrs(Request $request): JsonResponse
    {
        $data = $request->validate(['status' => 'required|in:buka,tutup']);
        Pengaturan::set('status_krs', $data['status']);
        return response()->json([
            'message' => $data['status'] === 'tutup' ? 'Pengisian KRS ditutup darurat untuk semua mahasiswa.' : 'Saklar darurat dilepas. KRS kembali mengikuti kalender.',
            'ringkasan' => $this->ringkasan(),
        ]);
    }

    private function bentuk(KegiatanAkademik $k): array
    {
        return [
            'id' => $k->id,
            'judul_kegiatan' => $k->judul_kegiatan,
            'deskripsi' => $k->deskripsi,
            'tanggal_mulai' => Carbon::parse($k->tanggal_mulai)->toDateString(),
            'tanggal_selesai' => Carbon::parse($k->tanggal_selesai)->toDateString(),
            'jenis' => $k->jenis ?? 'informasi',
            'jenis_label' => KalenderPengendali::JENIS[$k->jenis ?? 'informasi'] ?? $k->jenis,
            'pengendali' => KalenderPengendali::pengendali($k),
            'status' => KalenderPengendali::status($k),
            'tahun_akademik_id' => $k->tahun_akademik_id,
            'tahun_akademik' => $k->tahunAkademik?->nama_semester,
            'pmb_period_id' => $k->pmb_period_id,
            'pmb' => $k->pmbPeriod ? trim($k->pmbPeriod->nama_gelombang . ' ' . $k->pmbPeriod->tahun_pmb) : null,
            'target_roles' => $k->roles->pluck('id')->values(),
            'peran' => $k->roles->map(fn ($r) => $r->display_name ?: ucfirst($r->name))->values(),
        ];
    }

    /** Keadaan kendali untuk Tahun Akademik aktif + saklar darurat. */
    private function ringkasan(): array
    {
        $ta = TahunAkademik::where('is_active', true)->first();
        $krs = CekPeriodeKrsMiddleware::keadaan($ta);
        $jenis = [];
        foreach (KalenderPengendali::PER_TAHUN_AKADEMIK as $j) {
            $k = $ta ? KalenderPengendali::acara($j, $ta->id) : null;
            $jenis[] = [
                'jenis' => $j, 'label' => KalenderPengendali::JENIS[$j],
                'id' => $k?->id, 'status' => $k ? KalenderPengendali::status($k) : 'tidak_dijadwalkan',
                'mulai' => $k ? Carbon::parse($k->tanggal_mulai)->toDateString() : null,
                'selesai' => $k ? Carbon::parse($k->tanggal_selesai)->toDateString() : null,
                'acara' => $k ? $this->bentuk($k->load(['roles:id,name,display_name', 'tahunAkademik', 'pmbPeriod'])) : null,
            ];
        }
        return [
            'tahun_akademik' => $ta?->nama_semester,
            'saklar_krs' => $krs['saklar'] === 'tutup' ? 'tutup' : 'buka',
            'krs_buka' => $krs['buka'],
            'krs_pesan' => $krs['pesan'],
            'jenis' => $jenis,
        ];
    }
}
