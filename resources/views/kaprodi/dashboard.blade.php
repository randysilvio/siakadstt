@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold">Dashboard Program Studi</h2>
            <p class="text-muted mb-0">Selamat datang, Kaprodi <strong>{{ $programStudi->nama_prodi }}</strong></p>
        </div>
        <span class="badge bg-primary fs-6 shadow-sm">
            <i class="bi bi-building me-1"></i> {{ $programStudi->nama_prodi }}
        </span>
    </div>

    {{-- Statistik Ringkas --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-white h-100" style="background: linear-gradient(135deg, #0d6efd, #0a58ca);">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3">
                        <i class="bi bi-people-fill fs-3"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-uppercase small opacity-75">Total Mahasiswa</h6>
                        <span class="fs-3 fw-bold">{{ $programStudi->mahasiswas_count }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-white h-100" style="background: linear-gradient(135deg, #ffc107, #ffca2c);">
                <div class="card-body d-flex align-items-center text-dark">
                    <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3">
                        <i class="bi bi-hourglass-split fs-3"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-uppercase small opacity-75">Menunggu Validasi</h6>
                        @php
                            $menunggu = $mahasiswas->where('status_krs', 'Menunggu Persetujuan')->count();
                        @endphp
                        <span class="fs-3 fw-bold">{{ $menunggu }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-white h-100" style="background: linear-gradient(135deg, #198754, #157347);">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3">
                        <i class="bi bi-check-circle-fill fs-3"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-uppercase small opacity-75">KRS Disetujui</h6>
                        @php
                            $disetujui = $mahasiswas->where('status_krs', 'Disetujui')->count();
                        @endphp
                        <span class="fs-3 fw-bold">{{ $disetujui }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Mahasiswa --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-secondary"><i class="bi bi-list-task me-2"></i>Daftar Mahasiswa (Status KRS)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4">Mahasiswa</th>
                            <th>NIM</th>
                            <th>Email</th>
                            <th class="text-center">Status KRS</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mahasiswas as $m)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle text-secondary d-flex align-items-center justify-content-center me-3 border" style="width: 40px; height: 40px;">
                                        <i class="bi bi-person-fill fs-5"></i>
                                    </div>
                                    <span class="fw-bold text-dark">{{ $m->nama_lengkap }}</span>
                                </div>
                            </td>
                            <td class="font-monospace text-muted">{{ $m->nim }}</td>
                            <td class="small text-muted">{{ $m->user->email ?? '-' }}</td>
                            <td class="text-center">
                                @if($m->status_krs == 'Disetujui')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 rounded-pill"><i class="bi bi-check-lg me-1"></i> Disetujui</span>
                                @elseif($m->status_krs == 'Menunggu Persetujuan')
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-3 rounded-pill text-dark"><i class="bi bi-clock-history me-1"></i> Menunggu</span>
                                @elseif($m->status_krs == 'Ditolak')
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 rounded-pill"><i class="bi bi-x-lg me-1"></i> Ditolak</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 rounded-pill">Belum Mengisi</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($m->status_krs == 'Menunggu Persetujuan')
                                    <a href="{{ route('kaprodi.krs.show', $m->id) }}" class="btn btn-primary btn-sm shadow-sm px-3">
                                        <i class="bi bi-search me-1"></i> Validasi
                                    </a>
                                @else
                                    <a href="{{ route('kaprodi.krs.show', $m->id) }}" class="btn btn-outline-secondary btn-sm px-3">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-folder2-open fs-1 d-block mb-2 opacity-50"></i>
                                Belum ada data mahasiswa di program studi ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($mahasiswas->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $mahasiswas->links() }}
            </div>
        @endif
    </div>
</div>
@endsection