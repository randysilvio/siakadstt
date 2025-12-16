@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold">Detail Kartu Rencana Studi (KRS)</h2>
            <p class="text-muted">Validasi rencana studi mahasiswa bimbingan.</p>
        </div>
        <a href="{{ route('perwalian.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    {{-- INFO MAHASISWA & STATUS --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-primary">Informasi Mahasiswa</h5>
                    <table class="table table-borderless mb-0">
                        <tr><td width="150">Nama</td><td>: <strong>{{ $mahasiswa->nama_lengkap }}</strong></td></tr>
                        <tr><td>NIM</td><td>: {{ $mahasiswa->nim }}</td></tr>
                        <tr><td>Prodi</td><td>: {{ $mahasiswa->programStudi->nama_prodi }}</td></tr>
                        <tr><td>Status KRS</td><td>: 
                            @if($mahasiswa->status_krs == 'Disetujui') <span class="badge bg-success">Disetujui</span>
                            @elseif($mahasiswa->status_krs == 'Ditolak') <span class="badge bg-danger">Ditolak</span>
                            @else <span class="badge bg-warning text-dark">Menunggu</span>
                            @endif
                        </td></tr>
                    </table>
                </div>
            </div>
        </div>
        
        {{-- PANEL VALIDASI (AKSI DOSEN) --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 bg-light">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <h5 class="fw-bold mb-3">Aksi Validasi</h5>
                    
                    <form action="{{ route('perwalian.updateStatus', $mahasiswa->id) }}" method="POST">
                        @csrf @method('PATCH')
                        
                        <div class="d-grid gap-2">
                            <button type="submit" name="status_krs" value="Disetujui" class="btn btn-success fw-bold" {{ $mahasiswa->status_krs == 'Disetujui' ? 'disabled' : '' }}>
                                <i class="bi bi-check-circle-fill me-2"></i> SETUJUI KRS
                            </button>
                            
                            <button type="submit" name="status_krs" value="Ditolak" class="btn btn-danger" {{ $mahasiswa->status_krs == 'Ditolak' ? 'disabled' : '' }}>
                                <i class="bi bi-x-circle-fill me-2"></i> TOLAK / REVISI
                            </button>

                            @if($mahasiswa->status_krs != 'Menunggu Persetujuan')
                                <button type="submit" name="status_krs" value="Menunggu Persetujuan" class="btn btn-outline-secondary btn-sm mt-2">
                                    Reset ke Menunggu
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL MATA KULIAH --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Mata Kuliah Diambil ({{ $totalSks }} SKS)</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Kode MK</th>
                        <th>Nama Mata Kuliah</th>
                        <th class="text-center">SKS</th>
                        <th class="text-center">Smt</th>
                        <th>Jadwal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($krs as $mk)
                        <tr>
                            <td class="ps-4">{{ $mk->kode_mk }}</td>
                            <td>{{ $mk->nama_mk }}</td>
                            <td class="text-center">{{ $mk->sks }}</td>
                            <td class="text-center">{{ $mk->semester }}</td>
                            <td>
                                @foreach($mk->jadwals as $jadwal)
                                    <small class="d-block text-muted">{{ $jadwal->hari }}, {{ substr($jadwal->jam_mulai,0,5) }}-{{ substr($jadwal->jam_selesai,0,5) }}</small>
                                @endforeach
                            </td>
                            <td class="text-center">
                                {{-- Tombol Hapus Paksa (Revisi Dosen) --}}
                                <form action="{{ route('perwalian.krs.destroy', ['mahasiswa' => $mahasiswa->id, 'mk' => $mk->id]) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Hapus MK ini dari KRS mahasiswa?')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Mahasiswa belum mengambil mata kuliah.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection