@extends('layouts.app')

@section('content')
    <h1 class="mb-4">Dashboard Ketua Program Studi</h1>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            Informasi Program Studi
        </div>
        <div class="card-body">
            <h5>Anda adalah Kaprodi untuk: <strong>{{ $programStudi->nama_prodi }}</strong></h5>
            <p>Jumlah Mahasiswa Aktif: <strong>{{ $programStudi->mahasiswas_count }}</strong></p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Daftar Mahasiswa (Validasi KRS)
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>NIM</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Status KRS</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mahasiswas as $mahasiswa)
                    <tr>
                        <td>{{ $mahasiswa->nim }}</td>
                        <td>{{ $mahasiswa->nama_lengkap }}</td>
                        <td>{{ $mahasiswa->user->email ?? '-' }}</td>
                        <td>
                            @if($mahasiswa->status_krs == 'Disetujui')
                                <span class="badge bg-success">{{ $mahasiswa->status_krs }}</span>
                            @elseif($mahasiswa->status_krs == 'Menunggu Persetujuan')
                                <span class="badge bg-warning text-dark">{{ $mahasiswa->status_krs }}</span>
                            @elseif($mahasiswa->status_krs == 'Ditolak')
                                <span class="badge bg-danger">{{ $mahasiswa->status_krs }}</span>
                            @else
                                <span class="badge bg-secondary">{{ $mahasiswa->status_krs }}</span>
                            @endif
                        </td>
                        <td>
                            @if($mahasiswa->status_krs == 'Menunggu Persetujuan')
                            <a href="{{ route('kaprodi.krs.show', $mahasiswa->id) }}" class="btn btn-primary btn-sm">Validasi</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center">Belum ada mahasiswa di program studi ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $mahasiswas->links() }}
            </div>
        </div>
    </div>
@endsection