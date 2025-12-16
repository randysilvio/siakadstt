@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Header & Navigasi --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold">Validasi KRS Mahasiswa</h2>
            <p class="text-muted mb-0">Tinjau rencana studi mahasiswa semester ini.</p>
        </div>
        <a href="{{ route('kaprodi.dashboard') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>

    <div class="row">
        {{-- Kartu Profil Mahasiswa --}}
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center p-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-person-vcard fs-1"></i>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $mahasiswa->nama_lengkap }}</h5>
                    <p class="text-muted font-monospace mb-3">{{ $mahasiswa->nim }}</p>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Status Saat Ini:</span>
                        @if($mahasiswa->status_krs == 'Disetujui')
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Disetujui</span>
                        @elseif($mahasiswa->status_krs == 'Menunggu Persetujuan')
                            <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Menunggu</span>
                        @elseif($mahasiswa->status_krs == 'Ditolak')
                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Ditolak</span>
                        @else
                            <span class="badge bg-secondary">Draft</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Semester:</span>
                        <span class="fw-bold">{{ $mahasiswa->semester_berjalan ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel Mata Kuliah --}}
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-journal-check me-2"></i>Mata Kuliah Diambil</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-secondary">
                                <tr>
                                    <th class="ps-4">Kode</th>
                                    <th>Mata Kuliah</th>
                                    <th class="text-center">SKS</th>
                                    <th>Jadwal & Dosen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalSks = 0; @endphp
                                @forelse ($mahasiswa->mataKuliahs as $mk)
                                    <tr>
                                        <td class="ps-4 font-monospace text-muted small">{{ $mk->kode_mk }}</td>
                                        <td class="fw-bold text-dark">{{ $mk->nama_mk }}</td>
                                        <td class="text-center fw-bold">{{ $mk->sks }}</td>
                                        <td>
                                            <div class="small">
                                                @if($mk->dosen)
                                                    <div class="text-primary mb-1"><i class="bi bi-person-badge me-1"></i> {{ $mk->dosen->nama_lengkap }}</div>
                                                @endif

                                                @if($mk->jadwals->isNotEmpty())
                                                    <ul class="list-unstyled mb-0 text-muted" style="font-size: 0.85rem;">
                                                    @foreach($mk->jadwals as $jadwal)
                                                        <li><i class="bi bi-calendar-event me-1"></i> {{ $jadwal->hari }}, {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</li>
                                                    @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-muted fst-italic">- Jadwal belum diatur -</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @php $totalSks += $mk->sks; @endphp
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-cart-x fs-1 d-block mb-2 opacity-50"></i>
                                            Mahasiswa ini belum memilih mata kuliah.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="2" class="text-end fw-bold pe-3">Total SKS:</td>
                                    <td class="text-center fw-bold fs-5 text-primary">{{ $totalSks }}</td>
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
    <div class="card shadow-sm border-0">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-1">Tindakan Validasi</h6>
                <p class="text-muted small mb-0">Pastikan mata kuliah yang diambil sudah sesuai dengan kurikulum.</p>
            </div>
            <div class="d-flex gap-2">
                <form action="{{ route('kaprodi.krs.update', $mahasiswa->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menolak KRS ini? Mahasiswa harus merevisi KRS mereka.');">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status_krs" value="Ditolak">
                    <button type="submit" class="btn btn-danger px-4 shadow-sm" {{ $mahasiswa->status_krs == 'Ditolak' ? 'disabled' : '' }}>
                        <i class="bi bi-x-circle me-2"></i> Tolak KRS
                    </button>
                </form>

                <form action="{{ route('kaprodi.krs.update', $mahasiswa->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status_krs" value="Disetujui">
                    <button type="submit" class="btn btn-success px-4 shadow-sm" {{ $mahasiswa->status_krs == 'Disetujui' ? 'disabled' : '' }}>
                        <i class="bi bi-check-circle me-2"></i> Setujui KRS
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection