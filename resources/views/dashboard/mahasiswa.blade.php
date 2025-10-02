@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Dasbor Mahasiswa</h2>
            <p class="lead text-muted">Selamat datang kembali, {{ $mahasiswa->nama_lengkap }}</p>
        </div>
        <span class="badge bg-primary fs-6">Status KRS: {{ $mahasiswa->status_krs }}</span>
    </div>

    @if($memiliki_tagihan)
    <div class="alert alert-danger" role="alert">
        <strong>Perhatian!</strong> Anda memiliki tagihan pembayaran yang belum lunas. Segera selesaikan pembayaran untuk dapat mengisi KRS.
        <a href="{{ route('pembayaran.riwayat') }}" class="alert-link">Lihat Riwayat Pembayaran</a>.
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header fw-bold">Informasi Akademik</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="{{ $mahasiswa->foto_profil_url }}" alt="Foto Profil" class="img-fluid rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                        </div>
                        <div class="col-md-8">
                            <h4>{{ $mahasiswa->nama_lengkap }}</h4>
                            <p class="text-muted mb-1">{{ $mahasiswa->nim }}</p>
                            <p class="mb-2"><strong>Program Studi:</strong> {{ $mahasiswa->programStudi->nama_prodi }}</p>
                            <p><strong>Dosen Wali:</strong> {{ $mahasiswa->dosenWali->nama_lengkap ?? 'Belum Ditentukan' }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <h5 class="text-muted">IPK Kumulatif</h5>
                            <p class="fs-3 fw-bold">{{ number_format($ipk, 2) }}</p>
                        </div>
                        <div class="col-6">
                            <h5 class="text-muted">Total SKS Ditempuh</h5>
                            <p class="fs-3 fw-bold">{{ $total_sks }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header fw-bold">Jadwal Kuliah Semester Ini</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Hari</th>
                                    <th>Jam</th>
                                    <th>Mata Kuliah</th>
                                    <th>SKS</th>
                                    <th>Dosen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($jadwalKuliah as $jadwal)
                                    <tr>
                                        <td>{{ $jadwal->hari }}</td>
                                        <td>{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</td>
                                        <td>
                                            <strong>{{ $jadwal->mataKuliah->nama_mk }}</strong>
                                            <small class="d-block text-muted">{{ $jadwal->mataKuliah->kode_mk }}</small>
                                        </td>
                                        <td class="text-center">{{ $jadwal->mataKuliah->sks }}</td>
                                        <td>{{ $jadwal->mataKuliah->dosen->nama_lengkap ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">Anda belum mengambil KRS untuk semester ini atau KRS belum disetujui.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header fw-bold">Aksi Cepat</div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('krs.index') }}" class="list-group-item list-group-item-action">Isi / Lihat Kartu Rencana Studi (KRS)</a>
                    <a href="{{ route('khs.index') }}" class="list-group-item list-group-item-action">Lihat Kartu Hasil Studi (KHS)</a>
                    <a href="{{ route('transkrip.index') }}" class="list-group-item list-group-item-action">Lihat Transkrip Nilai</a>
                    <a href="{{ route('pembayaran.riwayat') }}" class="list-group-item list-group-item-action">Riwayat Pembayaran</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header fw-bold">Pengumuman Terbaru</div>
                <div class="list-group list-group-flush">
                    @forelse($pengumumans as $pengumuman)
                        {{-- PERBAIKAN: Mengarahkan ke rute publik yang baru --}}
                        <a href="{{ route('pengumuman.public.show', $pengumuman) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $pengumuman->judul }}</h6>
                                <small>{{ $pengumuman->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 small text-muted">{{ Str::limit(strip_tags($pengumuman->konten), 150) }}</p>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">Tidak ada pengumuman.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection