@extends('layouts.app')

@section('content')
@php
    $istilahPembimbing = \App\Models\Pengaturan::get('istilah_pembimbing', 'Dosen Tutor');
@endphp
<div class="container-fluid px-2 px-md-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 mt-2 gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Kartu Rencana Studi (KRS) <x-info-aturan kunci="krs_aturan" /> <x-info-aturan kunci="krs_gerbang" /></h3>
            <span class="text-muted small uppercase">Tahun Akademik {{ $periodeAktif->tahun }} ({{ strtoupper($periodeAktif->semester) }})</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($isLocked)
                <span class="badge bg-dark text-white px-3 py-2 uppercase fw-bold font-monospace">KRS DISETUJUI</span>
            @endif
            <a href="{{ route('krs.cetak.final') }}" target="_blank" class="btn btn-sm btn-dark px-3 py-2 uppercase fw-bold small shadow-sm">
                Cetak KRS (PDF)
            </a>
        </div>
    </div>
    
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border shadow-sm mb-3 p-3 small uppercase fw-bold">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border shadow-sm mb-3 p-3 small uppercase fw-bold">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(!empty($mahasiswa->catatan_kaprodi))
        <div class="alert bg-white border border-dark shadow-sm mb-3 p-3 small uppercase">
            <strong class="d-block text-dark mb-1">CATATAN {{ strtoupper($istilahPembimbing) }} / KAPRODI:</strong>
            <span class="font-monospace text-dark">"{{ $mahasiswa->catatan_kaprodi }}"</span>
        </div>
    @endif

    @error('mata_kuliahs')
        <div class="alert alert-danger py-2 px-3 small uppercase fw-bold shadow-sm mb-3">
            <ul class="mb-0 ps-3">
                @foreach ($errors->get('mata_kuliahs') as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @enderror

    {{-- RINGKASAN MAHASISWA & KUOTA SKS --}}
    <div class="row g-3 mb-3">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3 small">
                    <div class="row g-2 uppercase">
                        <div class="col-6 col-sm-4">
                            <span class="text-muted d-block" style="font-size: 10px;">NIM</span>
                            <strong class="font-monospace text-dark">{{ $mahasiswa->nim }}</strong>
                        </div>
                        <div class="col-6 col-sm-8">
                            <span class="text-muted d-block" style="font-size: 10px;">NAMA MAHASISWA</span>
                            <strong class="text-dark">{{ $mahasiswa->nama_lengkap }}</strong>
                        </div>
                        <div class="col-6 col-sm-4">
                            <span class="text-muted d-block" style="font-size: 10px;">STATUS KRS <x-info-aturan kunci="krs_persetujuan" /></span>
                            <strong class="font-monospace text-dark">{{ strtoupper($mahasiswa->status_krs ?? 'DRAFT') }}</strong>
                        </div>
                        <div class="col-6 col-sm-8">
                            <span class="text-muted d-block" style="font-size: 10px;">{{ strtoupper($istilahPembimbing) }}</span>
                            <strong class="text-dark">{{ optional($mahasiswa->dosenWali)->nama_lengkap ?? '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card wm-card-exclusive bg-dark text-white border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="uppercase small text-white-50 d-block font-monospace" style="font-size: 10px;">IPK SAAT INI: {{ number_format($ipk, 2) }} · SEMESTER KE-{{ $semester_ke ?? 1 }} <x-info-aturan kunci="ipk" terang /></span>
                        <span class="uppercase fw-bold small d-block">BEBAN SKS DIPILIH <x-info-aturan kunci="krs_batas_sks" terang /></span>
                    </div>
                    <div class="text-end font-monospace">
                        <h3 class="mb-0 fw-bold"><span id="total-sks">0</span> <small class="fs-6 fw-normal text-white-50">/ {{ $max_sks }} SKS</small></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PENCARIAN & FILTER SEMESTER --}}
    @php
        $semesterTersedia = $mata_kuliahs->pluck('semester')->unique()->sort()->values();
    @endphp
    <div class="card border-0 shadow-sm border-top border-dark border-4 mb-3 bg-light">
        <div class="card-body p-2.5 p-md-3">
            <div class="row g-2 align-items-center">
                <div class="col-lg-5">
                    <div class="input-group input-group-sm">
                        <input type="text" id="krsSmartSearch" class="form-control border-dark font-monospace uppercase" placeholder="Cari mata kuliah / kode / hari...">
                        <button type="button" class="btn btn-outline-dark" id="btnResetSearchKrs">Reset</button>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="d-flex flex-wrap gap-1 align-items-center justify-content-lg-end">
                        <button type="button" class="btn btn-sm btn-dark font-monospace fw-bold py-1 px-2 krs-filter-smt" data-smt="ALL" style="font-size: 11px;">SEMUA</button>
                        @foreach($semesterTersedia as $smt)
                            <button type="button" class="btn btn-sm btn-outline-dark bg-white font-monospace fw-bold py-1 px-2 krs-filter-smt" data-smt="{{ $smt }}" style="font-size: 11px;">SMT {{ $smt }}</button>
                        @endforeach
                        <button type="button" class="btn btn-sm btn-outline-dark bg-white font-monospace fw-bold py-1 px-2 krs-filter-smt" data-smt="TAKEN" style="font-size: 11px;">
                            TERPILIH
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('krs.store') }}" method="POST" id="formKrsMain">
        @csrf
        
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="tabelPilihKrs">
                        <thead class="bg-dark text-white small uppercase text-center">
                            <tr>
                                <th style="width: 50px;">PILIH</th>
                                <th style="width: 12%;">KODE</th>
                                <th class="text-start">MATA KULIAH</th>
                                <th style="width: 7%;">SKS</th>
                                <th style="width: 7%;">SMT</th>
                                <th class="text-start" style="width: 34%;">KELAS (ROMBEL) & JADWAL</th>
                                <th style="width: 8%;">HAPUS</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse ($mata_kuliahs as $mk)
                                @php
                                    $isTaken = in_array($mk->id, $mk_diambil_ids);
                                    $selectedKelas = $kelas_diambil_map[$mk->id] ?? ($mk->jadwals->first()->nama_kelas ?? 'A');
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
                                    $mkTerisiRows = isset($terisiMap[$mk->id]) ? $terisiMap[$mk->id]->keyBy('nama_kelas') : collect();
                                @endphp
                                <tr class="krs-row {{ $isTaken ? 'bg-light' : '' }}"
                                    data-smt="{{ $mk->semester }}"
                                    data-search="{{ strtolower($mk->kode_mk . ' ' . $mk->nama_mk . ' ' . $jadwalText) }}">
                                    <td class="text-center">
                                        <input class="form-check-input krs-checkbox border-dark" style="width: 1.25em; height: 1.25em;" type="checkbox" name="mata_kuliahs[]" value="{{ $mk->id }}"
                                               data-sks="{{ $mk->sks }}"
                                               data-jadwal='@json($mk->jadwals)'
                                               data-prasyarat-ok="{{ $prasyaratTerpenuhi ? 'true' : 'false' }}"
                                               {{ $isTaken ? 'checked' : '' }}
                                               {{ (!$prasyaratTerpenuhi || $isLocked) ? 'disabled' : '' }}>
                                    </td>
                                    <td class="text-center font-monospace fw-bold text-dark">{{ $mk->kode_mk }}</td>
                                    <td class="ps-2 ps-md-3">
                                        <strong class="uppercase text-dark d-block">{{ $mk->nama_mk }}</strong>
                                        @if(!$prasyaratTerpenuhi)
                                            <small class="d-block text-danger mt-1 uppercase fw-bold font-monospace" style="font-size: 10px;">PRASYARAT BELUM LULUS: {{ implode(', ', $prasyaratList) }}</small>
                                        @endif
                                        <small class="d-block text-danger mt-1 uppercase fw-bold font-monospace jadwal-clash-msg d-none" style="font-size: 10px;">JADWAL BENTROK DENGAN MATA KULIAH LAIN</small>
                                    </td>
                                    <td class="text-center font-monospace fw-bold">{{ $mk->sks }}</td>
                                    <td class="text-center font-monospace">{{ $mk->semester }}</td>
                                    <td class="ps-2 ps-md-3">
                                        @if($mk->jadwals->count() > 1)
                                            <select name="nama_kelas[{{ $mk->id }}]" class="form-select form-select-sm font-monospace fw-bold mb-1 krs-kelas-select" {{ $isLocked ? 'disabled' : '' }}>
                                                @foreach($mk->jadwals as $jadwal)
                                                    @php
                                                        $kls = $jadwal->nama_kelas ?? 'A';
                                                        $jmlTerisi = isset($mkTerisiRows[$kls]) ? $mkTerisiRows[$kls]->total : 0;
                                                        $kuota = $jadwal->kuota ?? 40;
                                                    @endphp
                                                    <option value="{{ $kls }}" {{ $selectedKelas == $kls ? 'selected' : '' }}>
                                                        KELAS {{ $kls }} — {{ strtoupper($jadwal->hari) }} {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} ({{ $jadwal->ruang ?? '-' }}) [{{ $jmlTerisi }}/{{ $kuota }}]
                                                    </option>
                                                @endforeach
                                            </select>
                                        @elseif($mk->jadwals->count() === 1)
                                            @php
                                                $jadwal = $mk->jadwals->first();
                                                $kls = $jadwal->nama_kelas ?? 'A';
                                                $jmlTerisi = isset($mkTerisiRows[$kls]) ? $mkTerisiRows[$kls]->total : 0;
                                                $kuota = $jadwal->kuota ?? 40;
                                            @endphp
                                            <input type="hidden" name="nama_kelas[{{ $mk->id }}]" class="krs-kelas-select" value="{{ $kls }}">
                                            <div class="font-monospace text-dark" style="font-size: 11px;">
                                                <strong>KELAS {{ $kls }}</strong> | {{ strtoupper($jadwal->hari) }}, {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}-{{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }} ({{ $jadwal->ruang ?? '-' }})
                                                <span class="text-muted">[{{ $jmlTerisi }}/{{ $kuota }}]</span>
                                            </div>
                                        @else
                                            <input type="hidden" name="nama_kelas[{{ $mk->id }}]" class="krs-kelas-select" value="A">
                                            <span class="text-muted font-monospace" style="font-size: 11px;">KELAS A (JADWAL MENYUSUL)</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($isTaken && !$isLocked)
                                            <button type="submit" form="form-delete-{{ $mk->id }}" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="return confirm('Hapus mata kuliah {{ $mk->nama_mk }} dari KRS?')">
                                                X
                                            </button>
                                        @elseif($isTaken && $isLocked)
                                            <span class="font-monospace fw-bold text-dark" style="font-size: 10px;">TERKUNCI</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted small uppercase fw-bold">Belum ada mata kuliah yang ditawarkan pada semester ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if(!$isLocked)
            <div class="card border-dark shadow-sm mb-5 position-sticky" style="bottom: 68px; z-index: 1020;">
                <div class="card-body p-2.5 p-md-3 bg-white d-flex justify-content-between align-items-center">
                    <div class="font-monospace small">
                        TERPILIH: <strong id="total-sks-bottom">0</strong> / {{ $max_sks }} SKS
                    </div>
                    <button type="submit" id="simpan-krs" class="btn btn-dark px-4 py-2 uppercase fw-bold small shadow-sm">
                        Simpan Rencana Studi (KRS)
                    </button>
                </div>
            </div>
        @endif
    </form>

    @foreach($mata_kuliahs as $mk)
        @if(in_array($mk->id, $mk_diambil_ids))
            <form id="form-delete-{{ $mk->id }}" action="{{ route('krs.destroy', $mk->id) }}" method="POST" class="d-none">
                @csrf @method('DELETE')
            </form>
        @endif
    @endforeach
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const allCheckboxes = document.querySelectorAll('.krs-checkbox');
    const allKelasSelects = document.querySelectorAll('.krs-kelas-select');
    const totalSksElement = document.getElementById('total-sks');
    const totalSksBottom  = document.getElementById('total-sks-bottom');
    const simpanButton = document.getElementById('simpan-krs');
    const maxSks = {{ $max_sks }};
    const isLocked = {{ $isLocked ? 'true' : 'false' }};

    function isTimeOverlap(jadwalA, jadwalB) {
        if (!jadwalA || !jadwalB || jadwalA.hari !== jadwalB.hari) return false;
        const dummyDate = '1970-01-01T';
        const startA = new Date(dummyDate + jadwalA.jam_mulai);
        const endA = new Date(dummyDate + jadwalA.jam_selesai);
        const startB = new Date(dummyDate + jadwalB.jam_mulai);
        const endB = new Date(dummyDate + jadwalB.jam_selesai);
        return startA < endB && endA > startB;
    }

    // Mengambil objek jadwal yang sesuai dengan pilihan Rombel Kelas (A/B) pada baris tersebut
    function getSelectedJadwalForRow(cb) {
        const jadwals = JSON.parse(cb.dataset.jadwal || '[]');
        if (jadwals.length === 0) return null;

        const tr = cb.closest('.krs-row');
        const selectEl = tr ? tr.querySelector('.krs-kelas-select') : null;
        const selectedKelas = selectEl ? String(selectEl.value || 'A').toUpperCase() : 'A';

        const found = jadwals.find(j => String(j.nama_kelas || 'A').toUpperCase() === selectedKelas);
        return found || jadwals[0];
    }

    function validateKRS() {
        let currentSks = 0;
        const checkedSchedules = [];

        allCheckboxes.forEach(cb => {
            const tr = cb.closest('.krs-row');
            if (cb.checked) {
                currentSks += parseInt(cb.dataset.sks);
                const activeSlot = getSelectedJadwalForRow(cb);
                if (activeSlot) {
                    checkedSchedules.push({ mkId: cb.value, slot: activeSlot });
                }
                if (tr) tr.classList.add('bg-light');
            } else {
                if (tr) tr.classList.remove('bg-light');
            }
        });

        if (totalSksElement) totalSksElement.textContent = currentSks;
        if (totalSksBottom) totalSksBottom.textContent = currentSks;

        let hasAnyClashAmongChecked = false;

        if (!isLocked) {
            allCheckboxes.forEach(cb => {
                const tr = cb.closest('.krs-row');
                const clashMsg = tr ? tr.querySelector('.jadwal-clash-msg') : null;
                if (cb.dataset.prasyaratOk === 'true') {
                    const rowSlot = getSelectedJadwalForRow(cb);
                    let isClashing = false;

                    if (rowSlot) {
                        for (const item of checkedSchedules) {
                            if (String(item.mkId) !== String(cb.value) && isTimeOverlap(rowSlot, item.slot)) {
                                isClashing = true;
                                break;
                            }
                        }
                    }

                    if (!cb.checked) {
                        cb.disabled = isClashing;
                        if (clashMsg) clashMsg.classList.toggle('d-none', !isClashing);
                    } else {
                        cb.disabled = false;
                        if (clashMsg) clashMsg.classList.toggle('d-none', !isClashing);
                        if (isClashing) hasAnyClashAmongChecked = true;
                    }
                }
            });
        }

        const isSksExceeded = currentSks > maxSks;
        if (simpanButton) {
            simpanButton.disabled = isSksExceeded || hasAnyClashAmongChecked;
        }
    }

    allCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', validateKRS);
    });

    allKelasSelects.forEach(sel => {
        sel.addEventListener('change', validateKRS);
    });

    validateKRS();

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

    if (searchInput) searchInput.addEventListener('input', filterKrsTable);
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