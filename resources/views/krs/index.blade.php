@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- Header & Aksi Utama --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 mt-3 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Pengisian Kartu Rencana Studi (KRS)</h3>
            <span class="text-muted small uppercase">Tahun Akademik {{ $periodeAktif->tahun }} - Semester {{ strtoupper($periodeAktif->semester) }}</span>
        </div>
        <div>
            @if($isLocked)
                <span class="badge bg-success rounded-0 fs-6 me-2 uppercase fw-bold font-monospace"><i class="bi bi-lock-fill me-1"></i> KRS DISETUJUI</span>
            @endif
            <a href="{{ route('krs.cetak.final') }}" class="btn btn-sm btn-dark rounded-0 px-4 py-2 uppercase fw-bold small shadow-sm">
                <i class="bi bi-printer-fill me-1"></i> Cetak KRS Resmi
            </a>
        </div>
    </div>
    
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-0 border shadow-sm mb-4 p-3 small uppercase fw-bold">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-0 border shadow-sm mb-4 p-3 small uppercase fw-bold">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(!empty($mahasiswa->catatan_kaprodi))
        <div class="alert alert-warning border-start border-warning border-5 rounded-0 shadow-sm mb-4 p-3 small uppercase">
            <strong class="d-block text-dark mb-1"><i class="bi bi-chat-left-quote-fill me-2"></i>CATATAN EVALUASI DOSEN TUTOR / KAPRODI:</strong>
            <span class="font-monospace text-dark">"{{ $mahasiswa->catatan_kaprodi }}"</span>
        </div>
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
                    Identitas Mahasiswa & Tutorship
                </div>
                <div class="card-body p-4 small">
                    <table class="table table-borderless mb-0 uppercase">
                        <tr>
                            <td class="fw-bold p-1" style="width: 150px;">NIM</td>
                            <td class="p-1 font-monospace fw-bold text-primary">: {{ $mahasiswa->nim }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold p-1">NAMA LENGKAP</td>
                            <td class="p-1 fw-bold text-dark">: {{ $mahasiswa->nama_lengkap }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold p-1">DOSEN TUTOR</td>
                            <td class="p-1">: {{ optional($mahasiswa->dosenWali)->nama_lengkap ?? 'BELUM DIPLOT' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold p-1 align-middle">STATUS KRS</td>
                            <td class="p-1 align-middle">:
                                @if($mahasiswa->status_krs == 'Disetujui')
                                    <span class="badge bg-success rounded-0 uppercase font-monospace fw-bold" style="font-size: 10px;">DISETUJUI</span>
                                @elseif($mahasiswa->status_krs == 'Menunggu Persetujuan')
                                    <span class="badge bg-warning text-dark rounded-0 uppercase font-monospace fw-bold" style="font-size: 10px;">MENUNGGU VALIDASI TUTOR / KAPRODI</span>
                                @elseif($mahasiswa->status_krs == 'Ditolak')
                                    <span class="badge bg-danger rounded-0 uppercase font-monospace fw-bold" style="font-size: 10px;">DITOLAK / PERLU REVISI</span>
                                @else
                                    <span class="badge bg-secondary rounded-0 uppercase font-monospace fw-bold" style="font-size: 10px;">{{ $mahasiswa->status_krs ?? 'DRAFT' }}</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-light border rounded-0 p-3 mb-3 shadow-sm">
                <h6 class="uppercase fw-bold small text-dark mb-2">Kuota Pengambilan SKS</h6>
                <div class="d-flex justify-content-between small font-monospace mb-1">
                    <span class="uppercase text-muted">IPK KUMULATIF SAAT INI:</span>
                    <strong class="text-primary">{{ number_format($ipk, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between small font-monospace">
                    <span class="uppercase text-muted">KUOTA MAKSIMAL SKS SEMESTER INI:</span>
                    <strong>{{ $max_sks }} SKS</strong>
                </div>
            </div>
            <div class="card bg-dark text-white border-0 rounded-0 shadow-sm">
                <div class="card-body p-3 d-flex justify-content-between align-items-center px-4">
                    <div>
                        <span class="uppercase small opacity-75 d-block font-monospace" style="font-size: 11px;">TOTAL BEBAN SKS DIPILIH</span>
                        <small class="text-white-50 font-monospace" style="font-size: 10px;">MAKSIMAL {{ $max_sks }} SKS</small>
                    </div>
                    <h3 class="mb-0 font-monospace fw-bold text-warning"><span id="total-sks">0</span> <span class="fs-6 fw-normal text-white">/ {{ $max_sks }} SKS</span></h3>
                </div>
            </div>
        </div>
    </div>

    {{-- SMART SEARCH & SMART FILTER BAR KRS --}}
    @php
        $semesterTersedia = $mata_kuliahs->pluck('semester')->unique()->sort()->values();
    @endphp
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-3 bg-light">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <div class="col-lg-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white rounded-0 border-dark"><i class="bi bi-search text-dark"></i></span>
                        <input type="text" id="krsSmartSearch" class="form-control rounded-0 border-dark font-monospace uppercase" placeholder="CARI NAMA MATA KULIAH / KODE MK / HARI JADWAL...">
                        <button type="button" class="btn btn-outline-dark rounded-0" id="btnResetSearchKrs" title="Bersihkan Pencarian"><i class="bi bi-x-lg"></i></button>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="d-flex flex-wrap gap-1 align-items-center justify-content-lg-end">
                        <span class="small font-monospace fw-bold text-muted me-1" style="font-size: 11px;">FILTER CEPAT:</span>
                        <button type="button" class="btn btn-sm btn-dark rounded-0 font-monospace fw-bold py-1 px-2 krs-filter-smt" data-smt="ALL" style="font-size: 11px;">SEMUA SMT</button>
                        @foreach($semesterTersedia as $smt)
                            <button type="button" class="btn btn-sm btn-outline-dark bg-white rounded-0 font-monospace fw-bold py-1 px-2 krs-filter-smt" data-smt="{{ $smt }}" style="font-size: 11px;">SMT {{ $smt }}</button>
                        @endforeach
                        <button type="button" class="btn btn-sm btn-outline-primary bg-white rounded-0 font-monospace fw-bold py-1 px-2 krs-filter-smt" data-smt="TAKEN" style="font-size: 11px;">
                            <i class="bi bi-check2-square me-1"></i>DIPILIH
                        </button>
                    </div>
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
                    <table class="table table-bordered align-middle mb-0" id="tabelPilihKrs">
                        <thead class="table-dark small uppercase text-center">
                            <tr>
                                <th style="width: 60px;">PILIH</th>
                                <th style="width: 14%;">KODE MK</th>
                                <th class="text-start">NAMA MATA KULIAH</th>
                                <th style="width: 8%;">SKS</th>
                                <th style="width: 8%;">SMT</th>
                                <th class="text-start" style="width: 30%;">JADWAL KULIAH</th>
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
                                    $jadwalText = $mk->jadwals->pluck('hari')->implode(' ');
                                @endphp
                                <tr class="krs-row {{ $isTaken ? 'table-primary' : '' }}"
                                    data-smt="{{ $mk->semester }}"
                                    data-search="{{ strtolower($mk->kode_mk . ' ' . $mk->nama_mk . ' ' . $jadwalText) }}">
                                    <td class="text-center">
                                        <input class="form-check-input krs-checkbox rounded-0 border-dark" type="checkbox" name="mata_kuliahs[]" value="{{ $mk->id }}"
                                               data-sks="{{ $mk->sks }}"
                                               data-jadwal='@json($mk->jadwals)'
                                               data-prasyarat-ok="{{ $prasyaratTerpenuhi ? 'true' : 'false' }}"
                                               {{ $isTaken ? 'checked' : '' }}
                                               {{ (!$prasyaratTerpenuhi || $isLocked) ? 'disabled' : '' }}>
                                    </td>
                                    <td class="text-center font-monospace fw-bold text-primary">{{ $mk->kode_mk }}</td>
                                    <td class="ps-3">
                                        <strong class="uppercase text-dark">{{ $mk->nama_mk }}</strong>
                                        @if(!$prasyaratTerpenuhi)
                                            <small class="d-block text-danger mt-1 uppercase fw-bold font-monospace" style="font-size: 10px;"><i class="bi bi-exclamation-circle me-1"></i> BELUM LULUS PRASYARAT: {{ implode(', ', $prasyaratList) }}</small>
                                        @endif
                                        <small class="d-block text-danger mt-1 uppercase fw-bold font-monospace jadwal-clash-msg d-none" style="font-size: 10px;"><i class="bi bi-slash-circle me-1"></i> JADWAL BENTROK DENGAN MATA KULIAH LAIN YANG DIPILIH</small>
                                    </td>
                                    <td class="text-center font-monospace fw-bold">{{ $mk->sks }}</td>
                                    <td class="text-center font-monospace">{{ $mk->semester }}</td>
                                    <td class="ps-3">
                                        @if($mk->jadwals->isNotEmpty())
                                            <ul class="list-unstyled mb-0 small font-monospace text-muted" style="font-size: 11px;">
                                            @foreach($mk->jadwals as $jadwal)
                                                <li><strong class="text-dark">{{ strtoupper($jadwal->hari) }}</strong>, {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}-{{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }} WIT ({{ $jadwal->ruang ?? 'TBA' }})</li>
                                            @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted font-monospace">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($isTaken && !$isLocked)
                                            <button type="submit" form="form-delete-{{ $mk->id }}" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2" onclick="return confirm('Hapus mata kuliah {{ $mk->nama_mk }} dari KRS?')" title="Hapus MK">
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
                <button type="submit" id="simpan-krs" class="btn btn-primary rounded-0 px-5 py-2 uppercase fw-bold small shadow-sm">
                    <i class="bi bi-save me-2"></i> Simpan Rencana Studi (KRS)
                </button>
            </div>
        @else
            <div class="alert alert-warning rounded-0 text-center py-3 uppercase small fw-bold text-dark mb-5 shadow-sm">
                <i class="bi bi-lock-fill me-1"></i> KRS Anda telah disetujui dan dikunci. Hubungi Dosen Tutor Anda jika ingin melakukan revisi mata kuliah.
            </div>
        @endif
    </form>

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
<script>
document.addEventListener('DOMContentLoaded', function () {
    const allCheckboxes = document.querySelectorAll('.krs-checkbox');
    const totalSksElement = document.getElementById('total-sks');
    const simpanButton = document.getElementById('simpan-krs');
    const maxSks = {{ $max_sks }};
    const isLocked = {{ $isLocked ? 'true' : 'false' }};

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
            const tr = cb.closest('.krs-row');
            if (cb.checked) {
                currentSks += parseInt(cb.dataset.sks);
                checkedSchedules.push(...JSON.parse(cb.dataset.jadwal));
                if (tr) tr.classList.add('table-primary');
            } else {
                if (tr) tr.classList.remove('table-primary');
            }
        });

        totalSksElement.textContent = currentSks;
        const isSksExceeded = currentSks > maxSks;
        
        if (simpanButton) {
            simpanButton.disabled = isSksExceeded;
        }
        totalSksElement.classList.toggle('text-danger', isSksExceeded);

        if (!isLocked) {
            allCheckboxes.forEach(cb => {
                const tr = cb.closest('.krs-row');
                const clashMsg = tr ? tr.querySelector('.jadwal-clash-msg') : null;
                if (cb.dataset.prasyaratOk === 'true') {
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
                        
                        cb.disabled = isClashing;
                        if (clashMsg) clashMsg.classList.toggle('d-none', !isClashing);
                    } else {
                        cb.disabled = false;
                        if (clashMsg) clashMsg.classList.add('d-none');
                    }
                }
            });
        }
    }

    allCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', validateKRS);
    });

    validateKRS();

    // SMART LIVE SEARCH & FILTER SEMESTER KRS
    let activeSmtFilter = 'ALL';
    const searchInput = document.getElementById('krsSmartSearch');
    const resetBtn = document.getElementById('btnResetSearchKrs');
    const krsRows = document.querySelectorAll('.krs-row');

    function filterKrsTable() {
        const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
        krsRows.forEach(row => {
            const smt = row.getAttribute('data-smt');
            const text = row.getAttribute('data-search') || '';
            const cb = row.querySelector('.krs-checkbox');
            const isChecked = cb ? cb.checked : false;

            let matchSmt = true;
            if (activeSmtFilter === 'TAKEN') {
                matchSmt = isChecked;
            } else if (activeSmtFilter !== 'ALL') {
                matchSmt = (smt === activeSmtFilter);
            }

            const matchText = (keyword === '') || text.includes(keyword);
            row.style.display = (matchSmt && matchText) ? '' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterKrsTable);
    }
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            searchInput.value = '';
            filterKrsTable();
            searchInput.focus();
        });
    }

    document.querySelectorAll('.krs-filter-smt').forEach(btn => {
        btn.addEventListener('click', function () {
            activeSmtFilter = this.getAttribute('data-smt');
            document.querySelectorAll('.krs-filter-smt').forEach(b => {
                b.classList.remove('btn-dark', 'text-white');
                b.classList.add('btn-outline-dark', 'bg-white');
            });
            this.classList.remove('btn-outline-dark', 'bg-white');
            this.classList.add('btn-dark', 'text-white');
            filterKrsTable();
        });
    });
});
</script>
@endpush