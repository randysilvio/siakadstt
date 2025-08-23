@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Evaluasi Dosen</h1>
    </div>

    <div class="alert alert-info">
        <strong>Informasi:</strong> Silakan berikan penilaian yang objektif terhadap kinerja dosen selama satu semester. Evaluasi Anda bersifat rahasia dan sangat berarti untuk peningkatan kualitas pembelajaran.
    </div>

    <div class="card">
        <div class="card-header">
            {{-- PERBAIKAN: Menampilkan nama sesi yang sebenarnya --}}
            Daftar Mata Kuliah untuk Sesi "{{ $sesiAktif->nama_sesi }}"
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Kode MK</th>
                            <th>Nama Mata Kuliah</th>
                            <th>Dosen Pengampu</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mataKuliah as $mk)
                            <tr>
                                <td>{{ $mk->kode_mk }}</td>
                                <td>{{ $mk->nama_mk }}</td>
                                <td>{{ $mk->dosen->nama_lengkap ?? 'Belum ditentukan' }}</td>
                                <td>
                                    @if (in_array($mk->id, $evaluasiSelesai))
                                        <span class="badge bg-success">Sudah Diisi</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Belum Diisi</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($mk->dosen)
                                        @if (in_array($mk->id, $evaluasiSelesai))
                                            <a href="{{ route('evaluasi.show', $mk->id) }}" class="btn btn-secondary btn-sm">Lihat/Edit</a>
                                        @else
                                            <a href="{{ route('evaluasi.show', $mk->id) }}" class="btn btn-primary btn-sm">Isi Kuesioner</a>
                                        @endif
                                    @else
                                        <button class="btn btn-secondary btn-sm" disabled>Dosen Belum Ada</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Anda tidak mengambil mata kuliah apapun pada semester ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
