@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- Header & Navigasi --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Validasi Rencana Studi</h3>
            <span class="text-muted small uppercase">Tinjau Rencana Studi Mahasiswa Semester Berjalan</span>
        </div>
        <a href="{{ route('kaprodi.dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 fw-bold uppercase small shadow-sm">
            Kembali ke Dashboard
        </a>
    </div>

    <div class="row g-4">
        {{-- Kartu Profil Mahasiswa --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-0 border-top border-dark border-4 h-100">
                <div class="card-body text-center p-4">
                    <div class="bg-light border text-dark d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-person-vcard fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark uppercase mb-1">{{ $mahasiswa->nama_lengkap }}</h5>
                    <p class="text-primary font-monospace fw-bold mb-3">{{ $mahasiswa->nim }}</p>
                    
                    <hr class="my-3 text-muted">
                    
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted uppercase fw-bold" style="font-size: 11px;">Status Saat Ini:</span>
                        @if($mahasiswa->status_krs == 'Disetujui')
                            <span class="badge bg-success rounded-0 uppercase fw-bold" style="font-size: 10px;">Disetujui</span>
                        @elseif($mahasiswa->status_krs == 'Menunggu Persetujuan')
                            <span class="badge bg-warning text-dark rounded-0 uppercase fw-bold" style="font-size: 10px;">Menunggu</span>
                        @elseif($mahasiswa->status_krs == 'Ditolak')
                            <span class="badge bg-danger rounded-0 uppercase fw-bold" style="font-size: 10px;">Ditolak</span>
                        @else
                            <span class="badge bg-secondary rounded-0 uppercase fw-bold" style="font-size: 10px;">{{ $mahasiswa->status_krs ?? 'DRAFT' }}</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted uppercase fw-bold" style="font-size: 11px;">Semester:</span>
                        <span class="fw-bold uppercase text-dark font-monospace" style="font-size: 11px;">{{ $mahasiswa->semester_berjalan ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel Mata Kuliah --}}
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-0 h-100">
                <div class="card-header bg-dark text-white rounded-0 py-3">
                    <h6 class="mb-0 uppercase fw-bold small">Daftar Mata Kuliah Yang Diambil</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 align-middle">
                            <thead class="bg-light small uppercase fw-bold text-center text-dark">
                                <tr>
                                    <th style="width: 15%;">KODE</th>
                                    <th class="text-start">MATA KULIAH</th>
                                    <th style="width: 10%;">SKS</th>
                                    <th class="text-start" style="width: 35%;">JADWAL & DOSEN</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                @php $totalSks = 0; @endphp
                                @forelse ($mahasiswa->mataKuliahs as $mk)
                                    <tr>
                                        <td class="text-center font-monospace fw-bold text-muted">{{ $mk->kode_mk }}</td>
                                        <td class="uppercase fw-bold text-dark">{{ $mk->nama_mk }}</td>
                                        <td class="text-center font-monospace fw-bold">{{ $mk->sks }}</td>
                                        <td>
                                            <div class="small">
                                                @if(isset($mk->dosen) && $mk->dosen)
                                                    <div class="text-primary fw-bold uppercase mb-1" style="font-size: 10px;"><i class="bi bi-person-badge me-1"></i> {{ $mk->dosen->nama_lengkap }}</div>
                                                @endif

                                                @if(isset($mk->jadwals) && $mk->jadwals->isNotEmpty())
                                                    <ul class="list-unstyled mb-0 text-muted font-monospace" style="font-size: 10px;">
                                                    @foreach($mk->jadwals as $jadwal)
                                                        <li><i class="bi bi-calendar-event me-1"></i> {{ strtoupper($jadwal->hari) }}, {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</li>
                                                    @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-muted uppercase" style="font-size: 10px;">- JADWAL BELUM DIATUR -</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @php $totalSks += $mk->sks; @endphp
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted small uppercase fw-bold">
                                            Mahasiswa ini belum memilih mata kuliah.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light fw-bold uppercase small text-dark">
                                <tr>
                                    <td colspan="2" class="text-end py-2 pe-3">TOTAL BEBAN SKS</td>
                                    <td class="text-center py-2 text-primary font-monospace fs-6">{{ $totalSks }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Aksi Validasi --}}
    <div class="card border-0 rounded-0 bg-light mt-4 shadow-sm">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center p-4 gap-3">
            <div>
                <h6 class="fw-bold mb-1 uppercase small text-dark">Tindakan Validasi Kaprodi</h6>
                <p class="text-muted small mb-0">Pastikan mata kuliah yang diambil sudah sesuai dengan kurikulum.</p>
            </div>
            <div class="d-flex gap-2">
                <form action="{{ route('kaprodi.krs.update', $mahasiswa->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menolak KRS ini? Mahasiswa harus merevisi KRS mereka.');">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status_krs" value="Ditolak">
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-0 fw-bold uppercase px-4 shadow-sm" {{ $mahasiswa->status_krs == 'Ditolak' ? 'disabled' : '' }}>
                        Tolak KRS
                    </button>
                </form>

                <form action="{{ route('kaprodi.krs.update', $mahasiswa->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status_krs" value="Disetujui">
                    <button type="submit" class="btn btn-success btn-sm rounded-0 fw-bold uppercase px-4 shadow-sm" {{ $mahasiswa->status_krs == 'Disetujui' ? 'disabled' : '' }}>
                        Setujui KRS
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection