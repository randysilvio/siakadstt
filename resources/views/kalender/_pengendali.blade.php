{{-- Jenis acara: informasi biasa, atau PENGENDALI yang otomatis membuka/menutup fitur sistem (App\Services\KalenderPengendali) --}}
@php
    $jenisDipilih = old('jenis', optional($kalender ?? null)->jenis ?? 'informasi');
    $taDipilih = old('tahun_akademik_id', optional($kalender ?? null)->tahun_akademik_id);
    $pmbDipilih = old('pmb_period_id', optional($kalender ?? null)->pmb_period_id);
@endphp
<div class="mb-4 p-3 border border-dark bg-light">
    <label for="jenis" class="form-label small fw-bold uppercase">Jenis Acara <x-info-aturan kunci="kalender_pengendali" /></label>
    <select name="jenis" id="jenis" class="form-select rounded-0 @error('jenis') is-invalid @enderror">
        @foreach(\App\Services\KalenderPengendali::JENIS as $kunci => $label)
            <option value="{{ $kunci }}" @selected($jenisDipilih === $kunci)>{{ $label }}</option>
        @endforeach
    </select>
    @error('jenis')<div class="invalid-feedback small">{{ $message }}</div>@enderror

    <div class="row g-3 mt-1" id="kendaliTa">
        <div class="col-md-8">
            <label for="tahun_akademik_id" class="form-label small fw-bold uppercase">Tahun Akademik yang dikendalikan</label>
            <select name="tahun_akademik_id" id="tahun_akademik_id" class="form-select rounded-0 @error('tahun_akademik_id') is-invalid @enderror">
                <option value="">— pilih —</option>
                @foreach($tahunAkademiks as $ta)
                    <option value="{{ $ta->id }}" @selected((string) $taDipilih === (string) $ta->id)>{{ $ta->nama_semester }}{{ $ta->is_active ? ' (aktif)' : '' }}</option>
                @endforeach
            </select>
            @error('tahun_akademik_id')<div class="invalid-feedback small">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="row g-3 mt-1" id="kendaliPmb">
        <div class="col-md-8">
            <label for="pmb_period_id" class="form-label small fw-bold uppercase">Gelombang PMB yang dikendalikan</label>
            <select name="pmb_period_id" id="pmb_period_id" class="form-select rounded-0 @error('pmb_period_id') is-invalid @enderror">
                <option value="">— pilih —</option>
                @foreach($pmbPeriods as $p)
                    <option value="{{ $p->id }}" @selected((string) $pmbDipilih === (string) $p->id)>{{ $p->nama_gelombang }} ({{ $p->tahun_pmb }})</option>
                @endforeach
            </select>
            @error('pmb_period_id')<div class="invalid-feedback small">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="small text-muted mt-2" id="kendaliKet"></div>
</div>
<script>
    (function () {
        var ket = {
            informasi: 'Hanya tampil di kalender & mengirim pemberitahuan. Tidak mengubah perilaku sistem.',
            krs: 'KRS hanya bisa diisi/diubah mahasiswa di rentang tanggal ini (tanggal selesai ikut dihitung sampai 23.59 WIT). Saklar "Status KRS = tutup" di Pengaturan tetap bisa menutup darurat. Untuk perpanjangan cukup geser tanggal selesai.',
            validasi_krs: 'Dosen Wali / Kaprodi hanya bisa menyetujui, menolak, atau membuka kembali KRS di rentang ini. Admin tetap bisa kapan saja.',
            edom: 'Sesi Evaluasi Dosen (EDOM) dibuat/diperbarui otomatis dan aktif di rentang ini.',
            input_nilai: 'Dosen hanya bisa menyimpan nilai di rentang ini; setelah lewat, nilai terkunci. Admin tetap bisa; perpanjangan cukup geser tanggal selesai.',
            pmb: 'Pendaftaran gelombang PMB terpilih dibuka & ditutup otomatis sesuai tanggal ini.'
        };
        var pilih = document.getElementById('jenis');
        function atur() {
            var j = pilih.value;
            document.getElementById('kendaliTa').style.display = ['krs', 'validasi_krs', 'edom', 'input_nilai'].indexOf(j) >= 0 ? '' : 'none';
            document.getElementById('kendaliPmb').style.display = j === 'pmb' ? '' : 'none';
            document.getElementById('kendaliKet').textContent = ket[j] || '';
        }
        pilih.addEventListener('change', atur);
        atur();
    })();
</script>
