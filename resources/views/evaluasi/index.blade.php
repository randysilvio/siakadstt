@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Evaluasi Dosen</h1>
    </div>

    {{-- ================================================================= --}}
    {{-- ===== PERBAIKAN DITAMBAHKAN DI SINI ===== --}}
    {{-- ================================================================= --}}
    {{-- Pesan ini akan muncul jika KRS mahasiswa belum disetujui Kaprodi --}}
    @if(Auth::user()->mahasiswa->status_krs !== 'Disetujui')
        <div class="alert alert-warning">
            <strong>Evaluasi Belum Bisa Dilakukan.</strong>
            <p class="mb-0">Anda baru dapat mengisi kuesioner evaluasi dosen setelah Kartu Rencana Studi (KRS) Anda berstatus <strong>"Disetujui"</strong> oleh Kepala Program Studi.</p>
        </div>
    @else
        <div class="alert alert-info">
            <strong>Informasi:</strong> Silakan berikan penilaian yang objektif terhadap kinerja dosen selama satu semester. Evaluasi Anda bersifat rahasia dan sangat berarti untuk peningkatan kualitas pembelajaran.
        </div>
    @endif
    {{-- ================================================================= --}}


    <div class="card">
        <div class="card-header">
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
                                {{-- Pesan ini akan muncul jika KRS belum disetujui atau jika tidak ada MK --}}
                                <td colspan="5" class="text-center">Tidak ada mata kuliah yang dapat dievaluasi saat ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
