@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 mt-3 gap-3">
        <div>
            <span class="text-muted font-monospace small uppercase">KODE: {{ $mataKuliah->kode_mk }} | SEMESTER {{ $mataKuliah->semester }} ({{$mataKuliah->sks }} SKS)</span>
            <h3 class="fw-bold text-dark mb-0 uppercase">Buku Nilai OBE: {{ $mataKuliah->nama_mk }}</h3>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(isset($hasVerumKelas) && $hasVerumKelas &&$mataKuliah->mahasiswas->isNotEmpty())
                <form action="{{ route('nilai.syncVerum', $mataKuliah->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tarik otomatis persentase Kehadiran dan rata-rata Nilai Tugas dari kelas E-Learning Verum semester ini?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success rounded-0 px-3 uppercase fw-bold small shadow-sm">
                        <i class="bi bi-cloud-arrow-down-fill me-1"></i> Tarik Kehadiran & Tugas dari Verum
                    </button>
                </form>
            @endif

            @if(Auth::user()->hasRole('admin'))
                <a href="{{ route('admin.nilai.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar MK
                </a>
            @else
                <a href="{{ route('dosen.dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
            @endif
        </div>
    </div>

    {{-- NOTIFIKASI SUKSES & ERROR --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3" role="alert">
            <div class="d-flex align-items-center small uppercase fw-bold">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i> {{ session('success') }}
            </div>
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3" role="alert">
            <div class="d-flex align-items-center small uppercase fw-bold">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i> {{ session('error') }}
            </div>
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- INFO RUMUS BOBOT OBE & PANDUAN KEYBOARD --}}
    <div class="card border-0 shadow-sm rounded-0 border-start border-dark border-4 mb-4 bg-light">
        <div class="card-body p-3 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 small">
            <div>
                <strong class="text-dark uppercase font-monospace"><i class="bi bi-calculator-fill me-1"></i> Formula Penilaian Standar OBE:</strong>
                <span class="text-muted font-monospace ms-1">
                    Kehadiran (10%) + Tugas (20%) + UTS (30%) + UAS (40%) = <strong>Nilai Akhir (0-100)</strong>
                </span>
            </div>
            <div class="font-monospace text-muted" style="font-size: 11px;">
                <span class="badge bg-dark text-white rounded-0 me-1">A: 80-100</span>
                <span class="badge bg-primary text-white rounded-0 me-1">B: 70-79.9</span>
                <span class="badge bg-info text-dark rounded-0 me-1">C: 60-69.9</span>
                <span class="badge bg-warning text-dark rounded-0 me-1">D: 50-59.9</span>
                <span class="badge bg-danger text-white rounded-0">E: &lt; 50</span>
            </div>
        </div>
    </div>

    <form action="{{ route('nilai.store') }}" method="POST" id="formBukuNilai">
        @csrf
        <input type="hidden" name="mata_kuliah_id" value="{{ $mataKuliah->id }}">
        
        {{-- TABEL INPUT DATA SPREADSHEET OBE --}}
        <div class="card border-0 shadow-sm rounded-0 mb-4 border-top border-dark border-4">
            <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
                <span class="uppercase fw-bold small"><i class="bi bi-table me-2"></i>Lembar Penilaian Peserta Kelas (DPNA)</span>
                <span class="badge bg-light text-dark rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 11px;">
                    TOTAL PESERTA: {{ $mataKuliah->mahasiswas->count() }} MAHASISWA
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="tabelNilaiObe">
                        <thead class="bg-light text-dark small uppercase text-center fw-bold">
                            <tr>
                                <th style="width: 5%;">NO</th>
                                <th style="width: 12%;">NIM</th>
                                <th class="text-start" style="width: 25%;">NAMA MAHASISWA</th>
                                <th style="width: 11%;">KEHADIRAN<br><span class="text-muted font-monospace" style="font-size: 10px;">(10%)</span></th>
                                <th style="width: 11%;">TUGAS<br><span class="text-muted font-monospace" style="font-size: 10px;">(20%)</span></th>
                                <th style="width: 11%;">UTS<br><span class="text-muted font-monospace" style="font-size: 10px;">(30%)</span></th>
                                <th style="width: 11%;">UAS<br><span class="text-muted font-monospace" style="font-size: 10px;">(40%)</span></th>
                                <th style="width: 7%;">SKOR<br><span class="text-muted font-monospace" style="font-size: 10px;">AKHIR</span></th>
                                <th style="width: 7%;">HURUF<br><span class="text-muted font-monospace" style="font-size: 10px;">MUTU</span></th>
                            </tr>
                        </thead>
                        <tbody class="small text-dark">
                            @forelse ($mataKuliah->mahasiswas as $index =>$mahasiswa)
                                @php
                                    $mhsId =$mahasiswa->id;
                                    $nHadir =$mahasiswa->pivot->nilai_kehadiran;
                                    $nTugas =$mahasiswa->pivot->nilai_tugas;
                                    $nUts   =$mahasiswa->pivot->nilai_uts;
                                    $nUas   =$mahasiswa->pivot->nilai_uas;
                                    $nAkhir =$mahasiswa->pivot->nilai_akhir_angka;
                                    $nHuruf =$mahasiswa->pivot->nilai ?? '';
                                @endphp
                                <tr class="baris-mahasiswa" data-mhs-id="{{ $mhsId }}">
                                    <td class="text-center font-monospace fw-bold text-muted">{{ $index + 1 }}</td>
                                    <td class="text-center font-monospace fw-bold text-primary">{{ $mahasiswa->nim }}</td>
                                    <td class="text-start ps-3 uppercase fw-bold text-dark">{{ $mahasiswa->nama_lengkap }}</td>
                                    <td class="p-1">
                                        <input type="number" step="0.01" min="0" max="100"
                                               name="nilai_kehadiran[{{ $mhsId }}]"
                                               value="{{ $nHadir !== null ? floatval($nHadir) : '' }}"
                                               class="form-control form-control-sm rounded-0 text-center font-monospace fw-bold input-komponen input-hadir"
                                               data-col="0" data-row="{{ $index }}" placeholder="0-100">
                                    </td>
                                    <td class="p-1">
                                        <input type="number" step="0.01" min="0" max="100"
                                               name="nilai_tugas[{{ $mhsId }}]"
                                               value="{{ $nTugas !== null ? floatval($nTugas) : '' }}"
                                               class="form-control form-control-sm rounded-0 text-center font-monospace fw-bold input-komponen input-tugas"
                                               data-col="1" data-row="{{ $index }}" placeholder="0-100">
                                    </td>
                                    <td class="p-1">
                                        <input type="number" step="0.01" min="0" max="100"
                                               name="nilai_uts[{{ $mhsId }}]"
                                               value="{{ $nUts !== null ? floatval($nUts) : '' }}"
                                               class="form-control form-control-sm rounded-0 text-center font-monospace fw-bold input-komponen input-uts"
                                               data-col="2" data-row="{{ $index }}" placeholder="0-100">
                                    </td>
                                    <td class="p-1">
                                        <input type="number" step="0.01" min="0" max="100"
                                               name="nilai_uas[{{ $mhsId }}]"
                                               value="{{ $nUas !== null ? floatval($nUas) : '' }}"
                                               class="form-control form-control-sm rounded-0 text-center font-monospace fw-bold input-komponen input-uas"
                                               data-col="3" data-row="{{ $index }}" placeholder="0-100">
                                    </td>
                                    <td class="text-center font-monospace fw-bold bg-light skor-akhir-display" id="skor-akhir-{{ $mhsId }}">
                                        {{ $nAkhir !== null ? number_format($nAkhir, 2) : '-' }}
                                    </td>
                                    <td class="p-1">
                                        <select name="nilai[{{ $mhsId }}]" id="huruf-{{ $mhsId }}" class="form-select form-select-sm rounded-0 font-monospace uppercase text-center fw-bold select-huruf">
                                            <option value="" {{ $nHuruf === '' ? 'selected' : '' }}>-</option>
                                            @foreach(['A', 'B', 'C', 'D', 'E'] as $h)
                                                <option value="{{ $h }}" {{ $nHuruf === $h ? 'selected' : '' }}>{{ $h }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 uppercase fw-bold text-muted">
                                        <i class="bi bi-people fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                        Belum ada mahasiswa dengan KRS Disetujui pada mata kuliah ini di semester aktif.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        {{-- BARIS TOMBOL AKSI --}}
        <div class="d-flex justify-content-between align-items-center mb-5">
            <span class="text-muted small font-monospace d-none d-md-inline">
                <i class="bi bi-keyboard me-1"></i> Tips: Gunakan tombol panah <strong>&uarr; &darr; &larr; &rarr;</strong> pada keyboard untuk berpindah sel seperti Excel.
            </span>
            <div class="ms-auto">
                @if(Auth::user()->hasRole('admin'))
                    <a href="{{ route('admin.nilai.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 py-2 uppercase fw-bold small me-2">Batal</a>
                @else
                    <a href="{{ route('dosen.dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 py-2 uppercase fw-bold small me-2">Batal</a>
                @endif
                
                @if($mataKuliah->mahasiswas->isNotEmpty())
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 py-2 uppercase fw-bold small shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan Buku Nilai
                    </button>
                @endif
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function hitungBaris(tr) {
            const mhsId = tr.getAttribute('data-mhs-id');
            const vHadir = tr.querySelector('.input-hadir').value;
            const vTugas = tr.querySelector('.input-tugas').value;
            const vUts   = tr.querySelector('.input-uts').value;
            const vUas   = tr.querySelector('.input-uas').value;

            const skorEl  = document.getElementById('skor-akhir-' + mhsId);
            const hurufEl = document.getElementById('huruf-' + mhsId);

            if (vHadir === '' && vTugas === '' && vUts === '' && vUas === '') {
                skorEl.textContent = '-';
                return;
            }

            const hadir = parseFloat(vHadir) || 0;
            const tugas = parseFloat(vTugas) || 0;
            const uts   = parseFloat(vUts) || 0;
            const uas   = parseFloat(vUas) || 0;

            const akhir = (hadir * 0.10) + (tugas * 0.20) + (uts * 0.30) + (uas * 0.40);
            skorEl.textContent = akhir.toFixed(2);

            let huruf = 'E';
            if (akhir >= 80) huruf = 'A';
            else if (akhir >= 70) huruf = 'B';
            else if (akhir >= 60) huruf = 'C';
            else if (akhir >= 50) huruf = 'D';

            hurufEl.value = huruf;
        }

        document.querySelectorAll('.baris-mahasiswa').forEach(function (tr) {
            tr.querySelectorAll('.input-komponen').forEach(function (inp) {
                inp.addEventListener('input', function () {
                    hitungBaris(tr);
                });
            });
        });

        // Navigasi Keyboard Spreadsheet (Atas, Bawah, Kiri, Kanan, Enter)
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