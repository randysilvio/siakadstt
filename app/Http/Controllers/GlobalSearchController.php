<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\SuratKeputusan;

class GlobalSearchController extends Controller
{
    /**
     * Endpoint AJAX Global Quick Search (Command Palette Ctrl + K).
     */
    public function search(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['results' => []], 401);
        }

        $q = trim((string) $request->input('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];
        $isAdmin = $user->hasRole('admin');
        $isDosen = $user->hasRole('dosen') || $user->isKaprodi();
        $dosen   = $user->dosen;

        // 1. PENCARIAN MAHASISWA (Sesuai Otoritas Role)
        if ($isAdmin || $isDosen || $user->hasRole('keuangan')) {
            $mhsQuery = Mahasiswa::with('programStudi')
                ->where(function ($sub) use ($q) {
                    $sub->where('nama_lengkap', 'like', "%{$q}%")
                        ->orWhere('nim', 'like', "%{$q}%");
                });

            if (!$isAdmin && !$user->hasRole('keuangan') && $dosen) {
                $prodiKaprodiIds = ProgramStudi::where('kaprodi_dosen_id', $dosen->id)->pluck('id')->toArray();
                $mhsQuery->where(function ($scope) use ($dosen, $prodiKaprodiIds) {
                    $scope->where('dosen_wali_id', $dosen->id);
                    if (!empty($prodiKaprodiIds)) {
                        $scope->orWhereIn('program_studi_id', $prodiKaprodiIds);
                    }
                });
            }

            $mahasiswas = $mhsQuery->orderBy('nama_lengkap')->limit(6)->get();

            foreach ($mahasiswas as $mhs) {
                $url = '#';
                $aksiLabel = 'Profil Mahasiswa';

                if ($isAdmin) {
                    $url = route('admin.mahasiswa.edit', $mhs->id);
                    $aksiLabel = 'Edit Master Mahasiswa';
                } elseif ($user->hasRole('keuangan')) {
                    $url = route('pembayaran.index', ['search' => $mhs->nim]);
                    $aksiLabel = 'Cek Tagihan Keuangan';
                } elseif ($dosen) {
                    if ((int) $mhs->dosen_wali_id === (int) $dosen->id) {
                        $url = route('perwalian.show', $mhs->id);
                        $aksiLabel = 'Buka KRS Tutorship';
                    } else {
                        $url = route('kaprodi.krs.show', $mhs->id);
                        $aksiLabel = 'Validasi KRS Kaprodi';
                    }
                }

                $results[] = [
                    'category' => 'MAHASISWA',
                    'title'    => $mhs->nama_lengkap . ' (' . $mhs->nim . ')',
                    'subtitle' => (optional($mhs->programStudi)->nama_prodi ?? '-') . ' | Status: ' . strtoupper($mhs->status_mahasiswa) . ' — ' . $aksiLabel,
                    'url'      => $url,
                    'icon'     => 'bi-person-vcard-fill',
                ];
            }
        }

        // 2. PENCARIAN MATA KULIAH & BUKU NILAI
        if ($isAdmin || $isDosen) {
            $mkQuery = MataKuliah::with('dosen')
                ->where(function ($sub) use ($q) {
                    $sub->where('nama_mk', 'like', "%{$q}%")
                        ->orWhere('kode_mk', 'like', "%{$q}%");
                });

            if (!$isAdmin && $dosen) {
                $pivotMkIds = DB::table('dosen_mata_kuliah')
                    ->where('dosen_id', $dosen->id)
                    ->pluck('mata_kuliah_id')
                    ->toArray();

                $mkQuery->where(function ($scope) use ($dosen, $pivotMkIds) {
                    $scope->where('dosen_id', $dosen->id)
                          ->orWhereIn('id', $pivotMkIds);
                });
            }

            $mataKuliahs = $mkQuery->orderBy('semester')->limit(5)->get();

            foreach ($mataKuliahs as $mk) {
                $url = $isAdmin ? route('admin.mata-kuliah.edit', $mk->id) : route('nilai.show', $mk->id);
                $aksi = $isAdmin ? 'Edit Mata Kuliah' : 'Buka Buku Nilai OBE';

                $results[] = [
                    'category' => 'MATA KULIAH',
                    'title'    => '[' . $mk->kode_mk . '] ' . $mk->nama_mk,
                    'subtitle' => 'Semester ' . $mk->semester . ' (' . $mk->sks . ' SKS) | Pengampu: ' . ($mk->dosen->nama_lengkap ?? '-') . ' — ' . $aksi,
                    'url'      => $url,
                    'icon'     => 'bi-journal-bookmark-fill',
                ];
            }
        }

        // 3. PENCARIAN DOSEN (Khusus Admin & Penjaminan Mutu)
        if ($isAdmin || $user->hasRole('penjaminan_mutu')) {
            $dosens = Dosen::with('programStudi')
                ->where(function ($sub) use ($q) {
                    $sub->where('nama_lengkap', 'like', "%{$q}%")
                        ->orWhere('nidn', 'like', "%{$q}%");
                })
                ->orderBy('nama_lengkap')
                ->limit(4)
                ->get();

            foreach ($dosens as $d) {
                $results[] = [
                    'category' => 'DOSEN',
                    'title'    => $d->nama_lengkap,
                    'subtitle' => 'NIDN: ' . ($d->nidn ?? '-') . ' | Homebase: ' . (optional($d->programStudi)->nama_prodi ?? 'STT GPI Papua'),
                    'url'      => $isAdmin ? route('admin.dosen.edit', $d->id) : ($d->nidn ? route('dosen.public.show', $d->nidn) : '#'),
                    'icon'     => 'bi-person-workspace',
                ];
            }
        }

        // 4. PENCARIAN SURAT KEPUTUSAN (SK)
        if ($isAdmin || $user->hasRole('administrasi_umum') || $isDosen) {
            $skQuery = SuratKeputusan::where(function ($sub) use ($q) {
                $sub->where('judul', 'like', "%{$q}%")
                    ->orWhere('nomor_surat', 'like', "%{$q}%");
            });

            if (!$isAdmin && !$user->hasRole('administrasi_umum') && $dosen) {
                $skQuery->whereHas('dosens', fn($rel) => $rel->where('dosens.id', $dosen->id))
                        ->where('status', 'Selesai');
            }

            $skList = $skQuery->latest()->limit(3)->get();

            foreach ($skList as $sk) {
                $results[] = [
                    'category' => 'ARSIP SK',
                    'title'    => $sk->judul,
                    'subtitle' => 'No: ' . ($sk->nomor_surat ?? '-') . ' | Jenis: ' . $sk->jenis_surat,
                    'url'      => ($isAdmin || $user->hasRole('administrasi_umum'))
                        ? route('administrasi.surat-keputusan.edit', $sk->id)
                        : route('surat-keputusan.download', $sk->id),
                    'icon'     => 'bi-file-earmark-pdf-fill',
                ];
            }
        }

        return response()->json(['results' => $results]);
    }
}