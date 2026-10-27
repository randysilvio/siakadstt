@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Manajemen Tutorship Akademik</h3>
            <span class="text-muted small uppercase">Pendampingan Mahasiswa Bimbingan Tutorship & Validasi Pengisian KRS</span>
        </div>
        <div>
            <div class="bg-light border border-dark border-opacity-25 rounded-0 px-4 py-2 d-flex align-items-center shadow-none">
                <i class="bi bi-people-fill text-dark fs-4 me-3"></i>
                <div>
                    <span class="small text-muted font-monospace uppercase d-block" style="font-size: 10px;">MAHASISWA TUTORSHIP AKTIF</span>
                    <span class="fs-5 fw-bold font-monospace text-dark">{{ $mahasiswa_wali->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- NOTIFIKASI SISTEM --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 p-3 mb-4 font-monospace small uppercase fw-bold shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 p-3 mb-4 font-monospace small uppercase fw-bold shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- TABEL 1: MAHASISWA TUTORSHIP SAAT INI (DENGAN SMART INSTANT FILTER) --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5 bg-white">
        <div class="card-header bg-white py-3 border-bottom rounded-0">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-md-5">
                    <span class="uppercase fw-bold small text-dark d-block">Daftar Mahasiswa Bimbingan Tutorship</span>
                    <div class="d-flex flex-wrap gap-1 mt-2" id="tutorStatusFilterGroup">
                        <button type="button" class="btn btn-sm btn-dark rounded-0 uppercase fw-bold py-0 px-2 tutor-status-btn" data-status="ALL" style="font-size: 10px;">Semua ({{ $mahasiswa_wali->count() }})</button>
                        <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-0 uppercase fw-bold py-0 px-2 tutor-status-btn" data-status="Menunggu Persetujuan" style="font-size: 10px;">Menunggu ({{ $mahasiswa_wali->where('status_krs_display', 'Menunggu Persetujuan')->count() }})</button>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-0 uppercase fw-bold py-0 px-2 tutor-status-btn" data-status="Disetujui" style="font-size: 10px;">Disetujui ({{ $mahasiswa_wali->where('status_krs_display', 'Disetujui')->count() }})</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-0 uppercase fw-bold py-0 px-2 tutor-status-btn" data-status="Belum Mengajukan" style="font-size: 10px;">Belum Isi ({{ $mahasiswa_wali->where('status_krs_display', 'Belum Mengajukan')->count() }})</button>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light rounded-0 border-dark"><i class="bi bi-search text-dark"></i></span>
                        <input type="text" id="liveSearchTutor" class="form-control rounded-0 border-dark font-monospace uppercase" placeholder="KETIK NAMA / NIM / PRODI MAHASISWA BIMBINGAN...">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="tabelMahasiswaTutor">
                    <thead class="bg-dark text-white small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 14%;">NIM</th>
                            <th class="text-start" style="width: 34%;">NAMA MAHASISWA & PRODI</th>
                            <th style="width: 12%;">ANGKATAN</th>
                            <th style="width: 10%;">IPK</th>
                            <th style="width: 14%;">STATUS KRS</th>
                            <th style="width: 16%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse ($mahasiswa_wali as $mahasiswa)
                            @php $stDisplay = $mahasiswa->status_krs_display ?? 'Belum Mengajukan'; @endphp
                            <tr class="baris-tutor" data-status="{{ $stDisplay }}" data-search="{{ strtolower($mahasiswa->nim . ' ' . $mahasiswa->nama_lengkap . ' ' . optional($mahasiswa->programStudi)->nama_prodi . ' ' . $mahasiswa->tahun_masuk) }}">
                                <td class="text-center font-monospace fw-bold text-primary">{{ $mahasiswa->nim }}</td>
                                <td class="text-start ps-3">
                                    <div class="uppercase fw-bold text-dark">{{ $mahasiswa->nama_lengkap }}</div>
                                    <span class="text-muted font-monospace" style="font-size: 11px;">{{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</span>
                                </td>
                                <td class="text-center font-monospace">{{ $mahasiswa->tahun_masuk }}</td>
                                <td class="text-center font-monospace fw-bold {{ ($mahasiswa->ipk_hitung > 0 && $mahasiswa->ipk_hitung < 2.00) ? 'text-danger' : 'text-dark' }}">
                                    {{ number_format($mahasiswa->ipk_hitung ?? 0, 2) }}
                                </td>
                                <td class="text-center">
                                    @if($stDisplay == 'Disetujui')
                                        <span class="badge bg-success text-white rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 10px;">DISETUJUI</span>
                                    @elseif($stDisplay == 'Menunggu Persetujuan')
                                        <span class="badge bg-warning text-dark rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 10px;">MENUNGGU</span>
                                    @elseif($stDisplay == 'Ditolak')
                                        <span class="badge bg-danger text-white rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 10px;">DITOLAK</span>
                                    @else
                                        <span class="badge bg-secondary text-white rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 10px;">BELUM ISI KRS</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group rounded-0" role="group">
                                        <a href="{{ route('perwalian.show', $mahasiswa->id) }}" class="btn btn-sm {{ $stDisplay == 'Menunggu Persetujuan' ? 'btn-primary' : 'btn-outline-dark' }} rounded-0 py-1 px-3 uppercase fw-bold small">
                                            <i class="bi bi-eye me-1"></i> {{ $stDisplay == 'Menunggu Persetujuan' ? 'Validasi' : 'Detail' }}
                                        </a>
                                        @if(in_array($mahasiswa->program_studi_id, $prodiKaprodiIds))
                                        <form action="{{ route('perwalian.destroy', $mahasiswa->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Lepas mahasiswa ini dari bimbingan Tutorship Anda?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2" title="Lepas Tutorship">
                                                <i class="bi bi-person-dash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-folder2-open fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    Anda belum memiliki alokasi mahasiswa bimbingan Tutorship.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- BAGIAN 2: PENETAPAN MAHASISWA TUTORSHIP (KHUSUS KAPRODI) --}}
    @if(!$bisaMenetapkan)
    <div class="alert alert-light border rounded-0 mt-5 small uppercase fw-bold text-muted">
        <i class="bi bi-info-circle me-2"></i> Penetapan mahasiswa bimbingan dilakukan oleh Ketua Program Studi atau Administrator.
    </div>
    @else
    <div class="mt-5 mb-3 border-bottom pb-2">
        <h5 class="fw-bold text-dark uppercase mb-1">Penetapan Mahasiswa Tutorship</h5>
        <span class="text-muted small uppercase font-monospace">Mahasiswa aktif program studi Anda yang belum memiliki Dosen Tutor</span>
    </div>

    {{-- Filter Bar Flat --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-light">
        <div class="card-body p-4">
            <form action="{{ route('perwalian.index') }}" method="GET" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold uppercase text-dark">Pencarian Mahasiswa</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white rounded-0"><i class="bi bi-search text-dark"></i></span>
                            <input type="text" name="search" class="form-control rounded-0 font-monospace uppercase" placeholder="KETIK NAMA / NIM..." value="{{ request('search') }}">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small fw-bold uppercase text-dark">Program Studi</label>
                        <select name="program_studi_id" class="form-select rounded-0 uppercase small fw-bold text-dark" onchange="submitFilter()">
                            <option value="">-- SEMUA PRODI --</option>
                            @foreach($program_studis as $prodi)
                                <option value="{{ $prodi->id }}" {{ request('program_studi_id') == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->nama_prodi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small fw-bold uppercase text-dark">Angkatan</label>
                        <select name="angkatan" class="form-select rounded-0 font-monospace text-dark fw-bold" onchange="submitFilter()">
                            <option value="">-- SEMUA ANGKATAN --</option>
                            @foreach($angkatans as $thn)
                                <option value="{{ $thn }}" {{ request('angkatan') == $thn ? 'selected' : '' }}>
                                    {{ $thn }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary rounded-0 flex-grow-1 uppercase fw-bold small py-2">Cari</button>
                        <a href="{{ route('perwalian.index') }}" class="btn btn-outline-dark rounded-0 px-3 py-2" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Mahasiswa Tersedia --}}
    <form action="{{ route('perwalian.store') }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm rounded-0 mb-5">
            <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
                <span class="uppercase fw-bold small">Daftar Mahasiswa Aktif Tersedia (Belum Memiliki Dosen Tutor)</span>
                <div class="d-flex gap-1">
                    <select name="dosen_wali_id" class="form-select form-select-sm rounded-0 uppercase small fw-bold" style="min-width: 220px;" required>
                        @foreach($dosenPilihan as $dp)
                            <option value="{{ $dp->id }}" {{ (int) $dp->id === (int) $dosen->id ? 'selected' : '' }}>{{ $dp->nama_lengkap }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm text-nowrap">
                        <i class="bi bi-plus-lg me-1"></i> Tetapkan Terpilih
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="bg-light text-dark small uppercase text-center fw-bold">
                            <tr>
                                <th style="width: 5%;">
                                    <input class="form-check-input rounded-0 border-dark" type="checkbox" id="checkAll">
                                </th>
                                <th style="width: 15%;">NIM</th>
                                <th class="text-start">NAMA LENGKAP MAHASISWA</th>
                                <th class="text-start" style="width: 30%;">PROGRAM STUDI</th>
                                <th style="width: 15%;">ANGKATAN</th>
                            </tr>
                        </thead>
                        <tbody class="small text-dark">
                            @forelse ($mahasiswa_tersedia as $mahasiswa)
                                <tr>
                                    <td class="text-center">
                                        <input class="form-check-input rounded-0 border-dark student-checkbox" type="checkbox" name="mahasiswa_ids[]" value="{{ $mahasiswa->id }}">
                                    </td>
                                    <td class="text-center font-monospace fw-bold text-primary">{{ $mahasiswa->nim }}</td>
                                    <td class="text-start uppercase fw-bold text-dark ps-3">{{ $mahasiswa->nama_lengkap }}</td>
                                    <td class="text-start uppercase text-muted ps-3">{{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</td>
                                    <td class="text-center font-monospace">{{ $mahasiswa->tahun_masuk }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 uppercase fw-bold text-muted">
                                        @if(request('search') || request('program_studi_id') || request('angkatan'))
                                            <i class="bi bi-search fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                            Tidak ditemukan data mahasiswa yang cocok dengan parameter filter.
                                        @else
                                            <i class="bi bi-check2-all fs-2 d-block mb-2 text-success"></i>
                                            Seluruh mahasiswa aktif telah memiliki alokasi Dosen Tutor di sistem.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if(method_exists($mahasiswa_tersedia, 'hasPages') && $mahasiswa_tersedia->hasPages())
                <div class="card-footer bg-white border-top py-3 rounded-0">
                    <div class="d-flex justify-content-center">
                        {{ $mahasiswa_tersedia->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </form>
    @endif
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Check All Klaim Mahasiswa
        const checkAll = document.getElementById('checkAll');
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = this.checked);
            });
        }

        // 2. Smart Instant Filter Tabel Mahasiswa Tutorship
        let activeStatusFilter = 'ALL';
        const searchInput = document.getElementById('liveSearchTutor');
        const rows = document.querySelectorAll('.baris-tutor');

        function filterTutorRows() {
            const keyword = (searchInput ? searchInput.value.toLowerCase().trim() : '');
            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                const rowText = row.getAttribute('data-search') || '';
                const matchStatus = (activeStatusFilter === 'ALL') || (rowStatus === activeStatusFilter);
                const matchKeyword = (keyword === '') || rowText.includes(keyword);
                row.style.display = (matchStatus && matchKeyword) ? '' : 'none';
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', filterTutorRows);
        }

        document.querySelectorAll('.tutor-status-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                activeStatusFilter = this.getAttribute('data-status');
                document.querySelectorAll('.tutor-status-btn').forEach(b => b.classList.remove('active', 'btn-dark', 'text-white'));
                this.classList.add('btn-dark', 'text-white');
                filterTutorRows();
            });
        });

        // 3. Pertahankan Posisi Scroll
        var scrollpos = sessionStorage.getItem('scrollPositionTutor');
        if (scrollpos) {
            window.scrollTo(0, parseInt(scrollpos));
            sessionStorage.removeItem('scrollPositionTutor');
        }
    });

    function submitFilter() {
        sessionStorage.setItem('scrollPositionTutor', window.scrollY);
        document.getElementById('filterForm').submit();
    }
</script>
@endsection