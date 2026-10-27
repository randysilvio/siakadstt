@extends('layouts.app')

@section('content')
@php
    $bobotDecimal = app(\App\Services\AcademicRuleService::class)->bobotPenilaian();
    $pctHadir = round($bobotDecimal['hadir'] * 100);
    $pctTugas = round($bobotDecimal['tugas'] * 100);
    $pctUts   = round($bobotDecimal['uts'] * 100);
    $pctUas   = round($bobotDecimal['uas'] * 100);
@endphp
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 mt-2 gap-3">
        <div>
            <span class="text-muted font-monospace small uppercase">{{ $mataKuliah->kode_mk }} | SEMESTER {{ $mataKuliah->semester }} ({{ $mataKuliah->sks }} SKS)</span>
            <h3 class="fw-bold text-dark mb-0 uppercase">Input Nilai: {{ $mataKuliah->nama_mk }}</h3>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(isset($hasVerumKelas) && $hasVerumKelas && $mataKuliah->mahasiswas->isNotEmpty())
                <form action="{{ route('nilai.syncVerum', $mataKuliah->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tarik data kehadiran dan nilai tugas dari kelas Verum?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-dark px-3 uppercase fw-bold small">
                        Tarik Data Verum
                    </button>
                </form>
            @endif

            @if(Auth::user()->hasRole('admin'))
                <a href="{{ route('admin.nilai.index') }}" class="btn btn-sm btn-outline-dark px-3 uppercase fw-bold small">Kembali</a>
            @else
                <a href="{{ route('dosen.dashboard') }}" class="btn btn-sm btn-outline-dark px-3 uppercase fw-bold small">Kembali</a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border shadow-sm mb-4 p-3 uppercase small fw-bold">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border shadow-sm mb-4 p-3 uppercase small fw-bold">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('nilai.store') }}" method="POST" id="formBukuNilai">
        @csrf
        <input type="hidden" name="mata_kuliah_id" value="{{ $mataKuliah->id }}">
        
        <div class="card border-0 shadow-sm mb-4 border-top border-dark border-4 bg-white">
            <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                <span class="uppercase fw-bold small">Daftar Nilai Peserta Kelas</span>
                <span class="badge bg-light text-dark font-monospace uppercase fw-bold px-2 py-1" style="font-size: 11px;">
                    {{ $mataKuliah->mahasiswas->count() }} MAHASISWA
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="tabelNilaiObe">
                        <thead class="bg-light text-dark small uppercase text-center fw-bold">
                            <tr>
                                <th style="width: 5%;">NO</th>
                                <th style="width: 11%;">NIM</th>
                                <th class="text-start" style="width: 22%;">NAMA MAHASISWA</th>
                                <th style="width: 7%;">KELAS</th>
                                <th style="width: 10%;">KEHADIRAN<br><span class="text-muted font-monospace" style="font-size: 10px;">({{ $pctHadir }}%)</span></th>
                                <th style="width: 10%;">TUGAS<br><span class="text-muted font-monospace" style="font-size: 10px;">({{ $pctTugas }}%)</span></th>
                                <th style="width: 10%;">UTS<br><span class="text-muted font-monospace" style="font-size: 10px;">({{ $pctUts }}%)</span></th>
                                <th style="width: 10%;">UAS<br><span class="text-muted font-monospace" style="font-size: 10px;">({{ $pctUas }}%)</span></th>
                                <th style="width: 7%;">ANGKA</th>
                                <th style="width: 8%;">HURUF</th>
                            </tr>
                        </thead>
                        <tbody class="small text-dark">
                            @forelse ($mataKuliah->mahasiswas as $index => $mahasiswa)
                                @php
                                    $mhsId = $mahasiswa->id;
                                    $nKelas = $mahasiswa->pivot->nama_kelas ?? 'A';
                                    $nHadir = $mahasiswa->pivot->nilai_kehadiran;
                                    $nTugas = $mahasiswa->pivot->nilai_tugas;
                                    $nUts   = $mahasiswa->pivot->nilai_uts;
                                    $nUas   = $mahasiswa->pivot->nilai_uas;
                                    $nAkhir = $mahasiswa->pivot->nilai_akhir_angka;
                                    $nHuruf = $mahasiswa->pivot->nilai ?? '';
                                    $nManual = (bool) ($mahasiswa->pivot->nilai_manual ?? false);
                                @endphp
                                <tr class="baris-mahasiswa" data-mhs-id="{{ $mhsId }}">
                                    <td class="text-center font-monospace fw-bold text-muted">{{ $index + 1 }}</td>
                                    <td class="text-center font-monospace fw-bold">{{ $mahasiswa->nim }}</td>
                                    <td class="text-start ps-3 uppercase fw-bold text-dark">{{ $mahasiswa->nama_lengkap }}</td>
                                    <td class="text-center font-monospace fw-bold">
                                        {{ $nKelas }}
                                        <input type="hidden" name="peserta[]" value="{{ $mhsId }}">
                                    </td>
                                    <td class="p-1">
                                        <input type="number" step="0.01" min="0" max="100" name="nilai_kehadiran[{{ $mhsId }}]" value="{{ $nHadir !== null ? floatval($nHadir) : '' }}" class="form-control form-control-sm text-center font-monospace fw-bold input-komponen input-hadir" data-col="0" data-row="{{ $index }}">
                                    </td>
                                    <td class="p-1">
                                        <input type="number" step="0.01" min="0" max="100" name="nilai_tugas[{{ $mhsId }}]" value="{{ $nTugas !== null ? floatval($nTugas) : '' }}" class="form-control form-control-sm text-center font-monospace fw-bold input-komponen input-tugas" data-col="1" data-row="{{ $index }}">
                                    </td>
                                    <td class="p-1">
                                        <input type="number" step="0.01" min="0" max="100" name="nilai_uts[{{ $mhsId }}]" value="{{ $nUts !== null ? floatval($nUts) : '' }}" class="form-control form-control-sm text-center font-monospace fw-bold input-komponen input-uts" data-col="2" data-row="{{ $index }}">
                                    </td>
                                    <td class="p-1">
                                        <input type="number" step="0.01" min="0" max="100" name="nilai_uas[{{ $mhsId }}]" value="{{ $nUas !== null ? floatval($nUas) : '' }}" class="form-control form-control-sm text-center font-monospace fw-bold input-komponen input-uas" data-col="3" data-row="{{ $index }}">
                                    </td>
                                    <td class="text-center font-monospace fw-bold bg-light" id="skor-akhir-{{ $mhsId }}">
                                        {{ $nAkhir !== null ? number_format($nAkhir, 2) : '-' }}
                                    </td>
                                    <td class="p-1">
                                        <select name="nilai[{{ $mhsId }}]" id="huruf-{{ $mhsId }}" class="form-select form-select-sm font-monospace uppercase text-center fw-bold select-huruf">
                                            <option value="" {{ $nHuruf === '' ? 'selected' : '' }}>-</option>
                                            @foreach(['A', 'B', 'C', 'D', 'E'] as $h)
                                                <option value="{{ $h }}" {{ $nHuruf === $h ? 'selected' : '' }}>{{ $h }}</option>
                                            @endforeach
                                        </select>
                                        {{-- Penanda huruf dipilih manual oleh dosen (tidak ditimpa konversi otomatis) --}}
                                        <input type="hidden" name="huruf_manual[{{ $mhsId }}]" id="manual-{{ $mhsId }}" value="{{ $nManual ? 1 : 0 }}">
                                        <div class="text-center font-monospace text-muted label-manual {{ $nManual ? '' : 'd-none' }}" id="label-manual-{{ $mhsId }}" style="font-size: 9px;">MANUAL</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5 uppercase fw-bold text-muted">
                                        Belum ada mahasiswa terdaftar pada mata kuliah ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-end mb-5">
            @if($mataKuliah->mahasiswas->isNotEmpty())
                <button type="submit" class="btn btn-dark px-5 py-2 uppercase fw-bold small shadow-sm">
                    Simpan Nilai Mahasiswa
                </button>
            @endif
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wHadir = {{ $bobotDecimal['hadir'] }};
        const wTugas = {{ $bobotDecimal['tugas'] }};
        const wUts   = {{ $bobotDecimal['uts'] }};
        const wUas   = {{ $bobotDecimal['uas'] }};

        function hitungBaris(tr) {
            const mhsId = tr.getAttribute('data-mhs-id');
            const vHadir = tr.querySelector('.input-hadir').value;
            const vTugas = tr.querySelector('.input-tugas').value;
            const vUts   = tr.querySelector('.input-uts').value;
            const vUas   = tr.querySelector('.input-uas').value;

            const skorEl  = document.getElementById('skor-akhir-' + mhsId);
            const hurufEl = document.getElementById('huruf-' + mhsId);

            const manualEl = document.getElementById('manual-' + mhsId);
            const isManual = manualEl && manualEl.value === '1';

            if (vHadir === '' && vTugas === '' && vUts === '' && vUas === '') {
                skorEl.textContent = '-';
                if (!isManual) hurufEl.value = '';
                return;
            }

            const hadir = parseFloat(vHadir) || 0;
            const tugas = parseFloat(vTugas) || 0;
            const uts   = parseFloat(vUts) || 0;
            const uas   = parseFloat(vUas) || 0;

            const akhir = (hadir * wHadir) + (tugas * wTugas) + (uts * wUts) + (uas * wUas);
            skorEl.textContent = akhir.toFixed(2);

            // Huruf mutu hanya dihitung otomatis setelah UTS/UAS terisi (sama dengan aturan server),
            // agar input kehadiran/tugas di tengah semester tidak menghasilkan nilai E prematur.
            if (isManual) return;
            if (vUts === '' && vUas === '') {
                hurufEl.value = '';
                return;
            }

            let huruf = 'E';
            if (akhir >= 80) huruf = 'A';
            else if (akhir >= 70) huruf = 'B';
            else if (akhir >= 60) huruf = 'C';
            else if (akhir >= 50) huruf = 'D';

            hurufEl.value = huruf;
        }

        // Dosen memilih huruf sendiri -> tandai manual; memilih "-" -> kembali ke perhitungan otomatis
        document.querySelectorAll('.select-huruf').forEach(function (sel) {
            sel.addEventListener('change', function () {
                const mhsId = this.id.replace('huruf-', '');
                const manualEl = document.getElementById('manual-' + mhsId);
                const labelEl = document.getElementById('label-manual-' + mhsId);
                const manual = this.value !== '';
                manualEl.value = manual ? '1' : '0';
                labelEl.classList.toggle('d-none', !manual);
                if (!manual) hitungBaris(this.closest('tr'));
            });
        });

        document.querySelectorAll('.baris-mahasiswa').forEach(function (tr) {
            tr.querySelectorAll('.input-komponen').forEach(function (inp) {
                inp.addEventListener('input', function () { hitungBaris(tr); });
            });
        });

        document.querySelectorAll('.input-komponen').forEach(function (input) {
            input.addEventListener('keydown', function (e) {
                const row = parseInt(this.getAttribute('data-row'));
                const col = parseInt(this.getAttribute('data-col'));
                let target = null;

                if (e.key === 'ArrowDown' || e.key === 'Enter') {
                    e.preventDefault();
                    target = document.querySelector(`.input-komponen[data-row="${row + 1}"][data-col="${col}"]`);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    target = document.querySelector(`.input-komponen[data-row="${row - 1}"][data-col="${col}"]`);
                } else if (e.key === 'ArrowRight' && this.selectionStart === this.value.length) {
                    target = document.querySelector(`.input-komponen[data-row="${row}"][data-col="${col + 1}"]`);
                } else if (e.key === 'ArrowLeft' && this.selectionStart === 0) {
                    target = document.querySelector(`.input-komponen[data-row="${row}"][data-col="${col - 1}"]`);
                }

                if (target) {
                    target.focus();
                    target.select();
                }
            });
        });
    });
</script>
@endsection