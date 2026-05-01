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

    {{-- Statistik Ringkas Atas (Biarkan sama seperti sebelumnya) --}}
    <!-- (Kode 3 kotak statistik di sini) -->

    {{-- TABEL & SMART FILTER --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-3 fw-bold text-secondary"><i class="bi bi-funnel me-2"></i>Filter Mahasiswa</h5>
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                
                {{-- 1. SMART FILTER TABS (STATUS) --}}
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('kaprodi.dashboard', ['search' => request('search')]) }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">
                        Semua <span class="badge bg-white text-primary ms-1 rounded-pill">{{ $countSemua }}</span>
                    </a>
                    
                    <a href="{{ route('kaprodi.dashboard', ['status' => 'Menunggu Persetujuan', 'search' => request('search')]) }}" class="btn btn-sm {{ request('status') == 'Menunggu Persetujuan' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }}">
                        Menunggu <span class="badge bg-white text-dark ms-1 rounded-pill">{{ $countMenunggu }}</span>
                    </a>
                    
                    <a href="{{ route('kaprodi.dashboard', ['status' => 'Disetujui', 'search' => request('search')]) }}" class="btn btn-sm {{ request('status') == 'Disetujui' ? 'btn-success' : 'btn-outline-success' }}">
                        Disetujui <span class="badge bg-white text-success ms-1 rounded-pill">{{ $countDisetujui }}</span>
                    </a>
                    
                    <a href="{{ route('kaprodi.dashboard', ['status' => 'Ditolak', 'search' => request('search')]) }}" class="btn btn-sm {{ request('status') == 'Ditolak' ? 'btn-danger' : 'btn-outline-danger' }}">
                        Ditolak <span class="badge bg-white text-danger ms-1 rounded-pill">{{ $countDitolak }}</span>
                    </a>
                    
                    <a href="{{ route('kaprodi.dashboard', ['status' => 'Belum', 'search' => request('search')]) }}" class="btn btn-sm {{ request('status') == 'Belum' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                        Belum Mengisi <span class="badge bg-white text-secondary ms-1 rounded-pill">{{ $countBelum }}</span>
                    </a>
                </div>

                {{-- 2. SMART SEARCH BAR (KOTAK PENCARIAN) --}}
                <form action="{{ route('kaprodi.dashboard') }}" method="GET" class="d-flex m-0">
                    <!-- Pertahankan status saat ini saat melakukan pencarian nama -->
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" name="search" class="form-control border-secondary" placeholder="Cari Nama/NIM..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                        
                        @if(request('search'))
                            <a href="{{ route('kaprodi.dashboard', ['status' => request('status')]) }}" class="btn btn-danger" title="Hapus Pencarian">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
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
                                <i class="bi bi-funnel-fill fs-1 d-block mb-2 opacity-25"></i>
                                Tidak ada data yang sesuai dengan filter/pencarian ini.
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