@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- Header & Aksi Utama --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Pengisian Kartu Rencana Studi (KRS)</h3>
            <span class="text-muted small uppercase">Tahun Akademik {{ $periodeAktif->tahun }} - Semester {{ $periodeAktif->semester }}</span>
        </div>
        <div>
            @if($isLocked)
                <span class="badge bg-success rounded-0 fs-6 me-2 uppercase fw-bold"><i class="bi bi-lock-fill me-1"></i> KRS Disetujui</span>
            @endif
            <a href="{{ route('krs.cetak.final') }}" class="btn btn-sm btn-dark rounded-0 px-4 uppercase fw-bold small shadow-sm">Cetak KRS (Resmi)</a>
        </div>
    </div>
    
    @if (session('success'))
        <div class="alert alert-success rounded-0 border-0 bg-success text-white py-2 px-3 small uppercase fw-bold shadow-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger rounded-0 border-0 bg-danger text-white py-2 px-3 small uppercase fw-bold shadow-sm">{{ session('error') }}</div>
    @endif

    @error('mata_kuliahs')
        <div class="alert alert-danger rounded-0 py-2 px-3 small uppercase fw-bold shadow-sm mb-3">
            <ul class="mb-0 ps-3">
                @foreach ($errors->get('mata_kuliahs') as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @enderror

    {{-- Panel Informasi Profil & Batas SKS --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    Data Mahasiswa
                </div>
                <div class="card-body p-4 font-monospace small">
                    <p class="mb-2"><strong class="font-sans-serif uppercase">NIM:</strong> <span class="text-primary fw-bold">{{ $mahasiswa->nim }}</span></p>
                    <p class="mb-2"><strong class="font-sans-serif uppercase">NAMA LENGKAP:</strong> {{ $mahasiswa->nama_lengkap }}</p>
                    <div class="d-flex align-items-center mt-3">
                        <strong class="font-sans-serif uppercase me-2">STATUS KRS:</strong> 
                        @if($mahasiswa->status_krs == 'Disetujui')
                            <span class="badge bg-success rounded-0 uppercase fw-bold" style="font-size: 10px;">Disetujui</span>
                        @elseif($mahasiswa->status_krs == 'Menunggu Persetujuan')
                            <span class="badge bg-warning text-dark rounded-0 uppercase fw-bold" style="font-size: 10px;">Menunggu Persetujuan</span>
                        @else
                            <span class="badge bg-secondary rounded-0 uppercase fw-bold" style="font-size: 10px;">{{ $mahasiswa->status_krs ?? 'DRAFT' }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-light border rounded-0 p-4 mb-3 shadow-sm">
                <h6 class="uppercase fw-bold small text-dark mb-2">Informasi Akademik</h6>
                <div class="d-flex justify-content-between small font-monospace mb-1">
                    <span class="font-sans-serif uppercase text-muted">IPK SAAT INI:</span>
                    <strong class="text-primary">{{ $ipk }}</strong>
                </div>
                <div class="d-flex justify-content-between small font-monospace">
                    <span class="font-sans-serif uppercase text-muted">BATAS PENGAMBILAN:</span>
                    <strong>{{ $max_sks }} SKS</strong>
                </div>
            </div>
            <div class="card bg-dark text-white border-0 rounded-0 shadow-sm">
                <div class="card-body p-3 text-center">
                    <span class="uppercase small opacity-75 d-block mb-1" style="font-size: 11px;">TOTAL SKS DIPILIH</span>
                    <h3 class="mb-0 font-monospace fw-bold text-warning"><span id="total-sks">0</span> <span class="fs-6 fw-normal text-white">SKS</span></h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulir Pemilihan Mata Kuliah --}}
    <form action="{{ route('krs.store') }}" method="POST">
        @csrf
        
        <div class="card border-0 shadow-sm rounded-0 mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-dark small uppercase text-center">
                            <tr>
                                <th style="width: 60px;">PILIH</th>
                                <th style="width: 15%;">KODE MK</th>
                                <th class="text-start">NAMA MATA KULIAH</th>
                                <th style="width: 8%;">SKS</th>
                                <th style="width: 8%;">SMT</th>
                                <th class="text-start" style="width: 30%;">JADWAL</th>
                                <th style="width: 8%;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse ($mata_kuliahs as $mk)
                                @php
                                    $isTaken = in_array($mk->id, $mk_diambil_ids);
                                    $prasyaratTerpenuhi = true;
                                    $prasyaratList = [];
                                    if ($mk->prasyarats->isNotEmpty()) {
                                        foreach ($mk->prasyarats as $prasyarat) {
                                            if (!in_array($prasyarat->id, $mk_lulus_ids)) {
                                                $prasyaratTerpenuhi = false;
                                                $prasyaratList[] = $prasyarat->nama_mk;
                                            }
                                        }
                                    }
                                @endphp
                                <tr class="krs-row {{ $isTaken ? 'table-primary' : '' }}">
                                    <td class="text-center">
                                        <input class="form-check-input krs-checkbox rounded-0 border-dark" type="checkbox" name="mata_kuliahs[]" value="{{ $mk->id }}"
                                               data-sks="{{ $mk->sks }}"
                                               data-jadwal='@json($mk->jadwals)'
                                               data-prasyarat-ok="{{ $prasyaratTerpenuhi ? 'true' : 'false' }}"
                                               {{ $isTaken ? 'checked' : '' }}
                                               {{ (!$prasyaratTerpenuhi || $isLocked) ? 'disabled' : '' }}>
                                    </td>
                                    <td class="text-center font-monospace fw-bold text-muted">{{ $mk->kode_mk }}</td>
                                    <td>
                                        <strong class="uppercase text-dark">{{ $mk->nama_mk }}</strong>
                                        @if(!$prasyaratTerpenuhi)
                                            <small class="d-block text-danger mt-1 uppercase fw-bold" style="font-size: 10px;"><i class="bi bi-exclamation-circle me-1"></i> PRASYARAT: {{ implode(', ', $prasyaratList) }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center font-monospace fw-bold">{{ $mk->sks }}</td>
                                    <td class="text-center font-monospace">{{ $mk->semester }}</td>
                                    <td>
                                        @if($mk->jadwals->isNotEmpty())
                                            <ul class="list-unstyled mb-0 small font-monospace text-muted" style="font-size: 11px;">
                                            @foreach($mk->jadwals as $jadwal)
                                                <li>{{ strtoupper($jadwal->hari) }}, {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}-{{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</li>
                                            @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($isTaken && !$isLocked)
                                            <button type="submit" form="form-delete-{{ $mk->id }}" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2" onclick="return confirm('Hapus mata kuliah {{ $mk->nama_mk }}?')" title="Hapus MK">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @elseif($isTaken && $isLocked)
                                            <span class="badge bg-success rounded-0 uppercase fw-bold" style="font-size: 10px;"><i class="bi bi-check-circle me-1"></i> FIX</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted small uppercase fw-bold">Tidak ada mata kuliah yang ditawarkan untuk semester ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if(!$isLocked)
            <div class="d-flex justify-content-end mb-5">
                <button type="submit" id="simpan-krs" class="btn btn-primary rounded-0 px-5 py-2 uppercase fw-bold small shadow-sm"><i class="bi bi-save me-2"></i> Simpan Perubahan KRS</button>
            </div>
        @else
            <div class="alert alert-warning rounded-0 text-center py-3 uppercase small fw-bold text-dark mb-5 shadow-sm">
                <i class="bi bi-lock-fill me-1"></i> KRS Anda telah disetujui oleh Dosen Wali. Hubungi dosen jika ingin melakukan perubahan.
            </div>
        @endif
    </form>

    {{-- Form Tersembunyi untuk Hapus per Item --}}
    @foreach($mata_kuliahs as $mk)
        @if(in_array($mk->id, $mk_diambil_ids))
            <form id="form-delete-{{ $mk->id }}" action="{{ route('krs.destroy', $mk->id) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @endif
    @endforeach
</div>
@endsection

@push('scripts')
{{-- Script Validasi Bawaan Utuh Tanpa Perubahan --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const allCheckboxes = document.querySelectorAll('.krs-checkbox');
    const totalSksElement = document.getElementById('total-sks');
    const simpanButton = document.getElementById('simpan-krs');
    const maxSks = {{ $max_sks }};

    function isTimeOverlap(jadwalA, jadwalB) {
        if (jadwalA.hari !== jadwalB.hari) return false;
        const dummyDate = '1970-01-01T';
        const startA = new Date(dummyDate + jadwalA.jam_mulai);
        const endA = new Date(dummyDate + jadwalA.jam_selesai);
        const startB = new Date(dummyDate + jadwalB.jam_mulai);
        const endB = new Date(dummyDate + jadwalB.jam_selesai);
        return startA < endB && endA > startB;
    }

    function validateKRS() {
        let currentSks = 0;
        const checkedSchedules = [];

        allCheckboxes.forEach(cb => {
            if (cb.checked) {
                currentSks += parseInt(cb.dataset.sks);
                checkedSchedules.push(...JSON.parse(cb.dataset.jadwal));
            }
        });

        totalSksElement.textContent = currentSks;
        const isSksExceeded = currentSks > maxSks;
        
        if(simpanButton) {
            simpanButton.disabled = isSksExceeded;
        }
        totalSksElement.classList.toggle('text-danger', isSksExceeded);

        allCheckboxes.forEach(cb => {
            if (cb.dataset.prasyaratOk === 'true' && !cb.disabled) { 
                if (!cb.checked) {
                    const jadwalMK = JSON.parse(cb.dataset.jadwal);
                    let isClashing = false;

                    for (const jadwal of jadwalMK) {
                        for (const checkedJadwal of checkedSchedules) {
                            if (isTimeOverlap(jadwal, checkedJadwal)) {
                                isClashing = true;
                                break;
                            }
                        }
                        if (isClashing) break;
                    }
                    
                    if(isClashing) cb.disabled = true;
                    else cb.disabled = false;
                }
            }
        });
    }

    allCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', validateKRS);
    });

    validateKRS();
});
</script>
@endpush