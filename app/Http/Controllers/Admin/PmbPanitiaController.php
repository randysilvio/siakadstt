<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PmbKepanitiaan;
use App\Models\PmbPeriod;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

/**
 * Admin menunjuk Panitia PMB per tahun penerimaan. Menutup kepanitiaan suatu tahun otomatis
 * mencabut akses seluruh anggotanya, tanpa perlu mengubah peran akun mereka.
 */
class PmbPanitiaController extends Controller
{
    public function index(Request $request)
    {
        $daftarTahun = PmbPeriod::whereNotNull('tahun_pmb')->distinct()->pluck('tahun_pmb')
            ->merge(PmbKepanitiaan::distinct()->pluck('tahun_pmb'))
            ->push((int) date('Y'))
            ->map(fn ($t) => (int) $t)->unique()->sortDesc()->values();

        $tahun = (int) $request->input('tahun', $daftarTahun->first());

        $anggota = PmbKepanitiaan::with(['user.roles', 'penunjuk'])
            ->where('tahun_pmb', $tahun)
            ->orderByRaw("CASE WHEN jabatan = 'Ketua' THEN 0 WHEN jabatan = 'Sekretaris' THEN 1 ELSE 2 END")
            ->orderBy('created_at')
            ->get();

        $tahunDitutup = $anggota->isNotEmpty() && $anggota->every(fn ($a) => $a->ditutup_pada !== null);

        // Kandidat panitia: akun internal (bukan mahasiswa/camaba) yang belum menjadi panitia tahun ini
        $kandidat = User::whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['mahasiswa', 'camaba']))
            ->whereNotIn('id', $anggota->pluck('user_id'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.pmb.panitia', compact('daftarTahun', 'tahun', 'anggota', 'tahunDitutup', 'kandidat'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun_pmb'     => 'required|integer|min:2000|max:2100',
            'jabatan'       => 'required|string|max:100',
            'mode'          => 'required|in:akun_lama,akun_baru',
            'user_id'       => 'required_if:mode,akun_lama|nullable|exists:users,id',
            'name'          => 'required_if:mode,akun_baru|nullable|string|max:255',
            'email'         => 'required_if:mode,akun_baru|nullable|email|max:255|unique:users,email',
            'password'      => ['required_if:mode,akun_baru', 'nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $tahun = (int) $request->tahun_pmb;

        $user = DB::transaction(function () use ($request) {
            if ($request->mode === 'akun_baru') {
                return User::create([
                    'name'     => strip_tags($request->name),
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                ]);
            }
            return User::findOrFail($request->user_id);
        });

        if ($user->hasRole(['mahasiswa', 'camaba'])) {
            return back()->withInput()->with('error', 'Akun mahasiswa/camaba tidak dapat ditunjuk sebagai Panitia PMB.');
        }

        $ditutup = PmbKepanitiaan::where('tahun_pmb', $tahun)->exists()
            && PmbKepanitiaan::where('tahun_pmb', $tahun)->aktif()->doesntExist();
        if ($ditutup) {
            return back()->withInput()->with('error', "Kepanitiaan PMB {$tahun} sudah ditutup. Buka kembali terlebih dahulu.");
        }

        PmbKepanitiaan::updateOrCreate(
            ['tahun_pmb' => $tahun, 'user_id' => $user->id],
            ['jabatan' => strip_tags($request->jabatan), 'ditunjuk_oleh' => Auth::id(), 'ditutup_pada' => null]
        );

        $user->notify(new GeneralNotification(
            'Penunjukan Panitia PMB ' . $tahun,
            'Anda ditunjuk sebagai ' . strip_tags($request->jabatan) . ' Panitia Penerimaan Mahasiswa Baru tahun ' . $tahun . '. Menu PMB kini tersedia di akun Anda.',
            route('admin.pmb.index'),
            'bi-person-badge-fill text-dark'
        ));

        return redirect()->route('admin.pmb-panitia.index', ['tahun' => $tahun])
            ->with('success', $user->name . ' ditetapkan sebagai ' . $request->jabatan . ' Panitia PMB ' . $tahun . '.');
    }

    public function update(Request $request, PmbKepanitiaan $panitia)
    {
        $request->validate(['jabatan' => 'required|string|max:100']);
        $panitia->update(['jabatan' => strip_tags($request->jabatan)]);

        return back()->with('success', 'Jabatan ' . $panitia->user->name . ' diperbarui.');
    }

    public function destroy(PmbKepanitiaan $panitia)
    {
        $tahun = $panitia->tahun_pmb;
        $nama = optional($panitia->user)->name;
        $panitia->delete();

        return redirect()->route('admin.pmb-panitia.index', ['tahun' => $tahun])
            ->with('success', $nama . ' dikeluarkan dari Panitia PMB ' . $tahun . '.');
    }

    /** Menutup kepanitiaan satu tahun: seluruh anggota kehilangan akses PMB. */
    public function tutup(Request $request)
    {
        $request->validate(['tahun_pmb' => 'required|integer']);
        $jumlah = PmbKepanitiaan::where('tahun_pmb', $request->tahun_pmb)->aktif()->update(['ditutup_pada' => now()]);

        return back()->with('success', "Kepanitiaan PMB {$request->tahun_pmb} ditutup. Akses {$jumlah} anggota panitia telah berakhir.");
    }

    public function buka(Request $request)
    {
        $request->validate(['tahun_pmb' => 'required|integer']);
        PmbKepanitiaan::where('tahun_pmb', $request->tahun_pmb)->update(['ditutup_pada' => null]);

        return back()->with('success', "Kepanitiaan PMB {$request->tahun_pmb} dibuka kembali.");
    }
}
