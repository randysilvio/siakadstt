@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 mt-3 gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Kepanitiaan PMB</h3>
            <span class="text-muted small uppercase">Penunjukan Panitia Penerimaan Mahasiswa Baru per Tahun Penerimaan</span>
        </div>
        <form action="{{ route('admin.pmb-panitia.index') }}" method="GET" class="d-flex gap-2 align-items-center">
            <label class="small fw-bold uppercase text-dark text-nowrap">Tahun PMB</label>
            <select name="tahun" class="form-select form-select-sm rounded-0 font-monospace fw-bold" onchange="this.form.submit()">
                @foreach($daftarTahun as $t)
                    <option value="{{ $t }}" {{ (int) $t === (int) $tahun ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger border rounded-0 shadow-sm mb-4 p-3 small fw-bold">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4 mb-5">
        {{-- DAFTAR PANITIA --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
                    <span class="uppercase fw-bold small">Susunan Panitia PMB {{ $tahun }}</span>
                    @if($anggota->isNotEmpty())
                        @if($tahunDitutup)
                            <span class="badge bg-secondary rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">DITUTUP</span>
                        @else
                            <span class="badge bg-success rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">AKTIF</span>
                        @endif
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="bg-light text-dark small uppercase text-center fw-bold">
                                <tr>
                                    <th style="width: 5%;">NO</th>
                                    <th class="text-start" style="width: 35%;">NAMA & AKUN</th>
                                    <th style="width: 30%;">JABATAN</th>
                                    <th style="width: 15%;">STATUS</th>
                                    <th style="width: 15%;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="small text-dark">
                                @forelse($anggota as $i => $a)
                                    <tr>
                                        <td class="text-center font-monospace fw-bold text-muted">{{ $i + 1 }}</td>
                                        <td class="text-start ps-3">
                                            <div class="fw-bold text-dark uppercase">{{ optional($a->user)->name ?? '-' }}</div>
                                            <div class="small text-muted font-monospace" style="font-size: 11px;">{{ optional($a->user)->email }}</div>
                                        </td>
                                        <td class="p-1">
                                            <form action="{{ route('admin.pmb-panitia.update', $a->id) }}" method="POST" class="d-flex gap-1">
                                                @csrf
                                                @method('PATCH')
                                                <input type="text" name="jabatan" value="{{ $a->jabatan }}" class="form-control form-control-sm rounded-0 uppercase" maxlength="100" required>
                                                <button type="submit" class="btn btn-sm btn-outline-dark rounded-0 px-2" title="Simpan Jabatan"><i class="bi bi-check-lg"></i></button>
                                            </form>
                                        </td>
                                        <td class="text-center">
                                            @if($a->ditutup_pada)
                                                <span class="badge bg-secondary text-white rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">BERAKHIR</span>
                                            @else
                                                <span class="badge bg-success text-white rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">AKTIF</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('admin.pmb-panitia.destroy', $a->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Keluarkan {{ addslashes(optional($a->user)->name) }} dari Panitia PMB {{ $tahun }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2" title="Keluarkan">
                                                    <i class="bi bi-person-dash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 uppercase fw-bold text-muted">
                                            <i class="bi bi-people fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                            Belum ada panitia yang ditunjuk untuk PMB {{ $tahun }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($anggota->isNotEmpty())
                    <div class="card-footer bg-light border-top py-3 rounded-0 d-flex justify-content-between align-items-center gap-2 flex-wrap">
                        <span class="text-muted small uppercase">
                            Menutup kepanitiaan langsung mengakhiri akses seluruh anggota ke modul PMB.
                        </span>
                        @if($tahunDitutup)
                            <form action="{{ route('admin.pmb-panitia.buka') }}" method="POST" onsubmit="return confirm('Buka kembali kepanitiaan PMB {{ $tahun }}?');">
                                @csrf
                                <input type="hidden" name="tahun_pmb" value="{{ $tahun }}">
                                <button type="submit" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">Buka Kembali</button>
                            </form>
                        @else
                            <form action="{{ route('admin.pmb-panitia.tutup') }}" method="POST" onsubmit="return confirm('Tutup kepanitiaan PMB {{ $tahun }}? Seluruh anggota tidak lagi dapat mengakses modul PMB.');">
                                @csrf
                                <input type="hidden" name="tahun_pmb" value="{{ $tahun }}">
                                <button type="submit" class="btn btn-sm btn-danger rounded-0 px-3 uppercase fw-bold small">Tutup Kepanitiaan {{ $tahun }}</button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- FORM PENUNJUKAN --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-0 border-top border-primary border-4">
                <div class="card-header bg-white rounded-0 py-3 border-bottom">
                    <span class="uppercase fw-bold small text-dark">Tunjuk Anggota Panitia</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.pmb-panitia.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tahun_pmb" value="{{ $tahun }}">

                        <div class="mb-3">
                            <label class="form-label small fw-bold uppercase text-dark">Jabatan <span class="text-danger">*</span></label>
                            <input type="text" name="jabatan" class="form-control rounded-0 uppercase" list="daftarJabatan" value="{{ old('jabatan', 'Anggota') }}" maxlength="100" required>
                            <datalist id="daftarJabatan">
                                <option value="Ketua">
                                <option value="Sekretaris">
                                <option value="Bendahara">
                                <option value="Verifikator Berkas">
                                <option value="Anggota">
                            </datalist>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold uppercase text-dark">Sumber Akun</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode" id="modeLama" value="akun_lama" {{ old('mode', 'akun_lama') === 'akun_lama' ? 'checked' : '' }}>
                                    <label class="form-check-label small uppercase fw-bold" for="modeLama">Akun yang ada</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode" id="modeBaru" value="akun_baru" {{ old('mode') === 'akun_baru' ? 'checked' : '' }}>
                                    <label class="form-check-label small uppercase fw-bold" for="modeBaru">Akun khusus baru</label>
                                </div>
                            </div>
                        </div>

                        <div id="blokAkunLama" class="mb-3">
                            <label class="form-label small fw-bold uppercase text-dark">Pilih Dosen / Pegawai</label>
                            <select name="user_id" class="form-select rounded-0 uppercase small">
                                <option value="">-- PILIH AKUN --</option>
                                @foreach($kandidat as $k)
                                    <option value="{{ $k->id }}" {{ old('user_id') == $k->id ? 'selected' : '' }}>{{ $k->name }} ({{ $k->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="blokAkunBaru" class="mb-3 d-none">
                            <div class="mb-2">
                                <label class="form-label small fw-bold uppercase text-dark">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control rounded-0 uppercase" value="{{ old('name') }}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold uppercase text-dark">Email Login</label>
                                <input type="email" name="email" class="form-control rounded-0 font-monospace" value="{{ old('email') }}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold uppercase text-dark">Kata Sandi</label>
                                <input type="password" name="password" class="form-control rounded-0" autocomplete="new-password">
                            </div>
                            <div>
                                <label class="form-label small fw-bold uppercase text-dark">Ulangi Kata Sandi</label>
                                <input type="password" name="password_confirmation" class="form-control rounded-0" autocomplete="new-password">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary rounded-0 w-100 uppercase fw-bold small py-2 shadow-sm" {{ $tahunDitutup ? 'disabled' : '' }}>
                            <i class="bi bi-person-plus-fill me-1"></i> Tetapkan Panitia {{ $tahun }}
                        </button>
                        <div class="form-text text-muted small uppercase mt-2">
                            Panitia dapat memverifikasi bayar formulir, memeriksa berkas, meluluskan/menolak, mengekspor data, dan mencetak kartu ujian pendaftar tahun {{ $tahun }}.
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function aturMode() {
            const baru = document.getElementById('modeBaru').checked;
            document.getElementById('blokAkunBaru').classList.toggle('d-none', !baru);
            document.getElementById('blokAkunLama').classList.toggle('d-none', baru);
        }
        document.querySelectorAll('input[name="mode"]').forEach(r => r.addEventListener('change', aturMode));
        aturMode();
    });
</script>
@endsection
