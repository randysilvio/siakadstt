@extends('layouts.app')

@section('content')
<div class="container">
    @if(isset($dosen))
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Dasbor Dosen</h2>
                <p class="lead text-muted">Selamat datang kembali, {{ $dosen->nama_lengkap }}</p>
            </div>
            <div>
                {{-- Menampilkan badge untuk setiap peran yang dimiliki --}}
                @foreach(Auth::user()->roles as $role)
                    <span class="badge bg-primary fs-6 me-1">{{ $role->display_name }}</span>
                @endforeach
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                {{-- Mata Kuliah yang Diampu --}}
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Mata Kuliah Semester Ini</h5></div>
                    <ul class="list-group list-group-flush">
                        @forelse($mata_kuliahs as $mk)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $mk->kode_mk }}</strong> - {{ $mk->nama_mk }} <br>
                                    <small>{{ $mk->sks }} SKS - {{ $mk->mahasiswas_count }} Mahasiswa</small>
                                </div>
                                <a href="{{ route('nilai.show', $mk) }}" class="btn btn-sm btn-outline-primary">Input Nilai</a>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted">Anda tidak mengajar mata kuliah apapun semester ini.</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Jadwal Mengajar --}}
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Jadwal Mengajar</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hari</th>
                                        <th>Jam</th>
                                        <th>Mata Kuliah</th>
                                        <th>Kode MK</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($jadwalKuliah as $jadwal)
                                        <tr>
                                            <td>{{ $jadwal->hari }}</td>
                                            <td>{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</td>
                                            <td>{{ $jadwal->mataKuliah->nama_mk }}</td>
                                            <td>{{ $jadwal->mataKuliah->kode_mk }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-3">Tidak ada jadwal mengajar.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- Portal Multi-Peran --}}
                <div class="card mb-4">
                    <div class="card-header fw-bold">Portal Peran Anda</div>
                    <div class="list-group list-group-flush">
                        {{-- Selalu tampil untuk Dosen --}}
                        <a href="{{ route('perwalian.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Perwalian Akademik <span class="badge bg-primary rounded-pill">{{ $jumlahMahasiswaWali }} Mhs</span>
                        </a>
                        
                        {{-- Muncul jika user adalah Kaprodi --}}
                        @if(Auth::user()->hasRole('kaprodi') && isset($dataKaprodi))
                            <a href="{{ route('kaprodi.dashboard') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center list-group-item-info">
                                <div>
                                    <strong>Portal Kaprodi</strong>
                                    <small class="d-block">{{ $dataKaprodi['prodi']->nama_prodi }}</small>
                                </div>
                                @if($dataKaprodi['krs_count'] > 0)
                                    <span class="badge bg-danger rounded-pill">{{ $dataKaprodi['krs_count'] }} KRS</span>
                                @endif
                            </a>
                        @endif

                        {{-- Muncul jika user adalah Pimpinan/Rektorat --}}
                        @if(Auth::user()->hasRole('rektorat'))
                            <a href="{{ route('rektorat.dashboard') }}" class="list-group-item list-group-item-action list-group-item-dark"><strong>Dasbor Pimpinan</strong></a>
                        @endif
                        
                        {{-- Muncul jika user adalah Penjaminan Mutu --}}
                        @if(Auth::user()->hasRole('penjaminan_mutu'))
                            <a href="{{ route('mutu.dashboard') }}" class="list-group-item list-group-item-action list-group-item-secondary"><strong>Dasbor Penjaminan Mutu</strong></a>
                        @endif

                        {{-- Muncul jika user adalah Keuangan --}}
                        @if(Auth::user()->hasRole('keuangan'))
                            <a href="{{ route('pembayaran.index') }}" class="list-group-item list-group-item-action list-group-item-success"><strong>Manajemen Pembayaran</strong></a>
                        @endif

                        {{-- Muncul jika user adalah Pustakawan --}}
                        @if(Auth::user()->hasRole('pustakawan'))
                            <a href="{{ route('perpustakaan.koleksi.index') }}" class="list-group-item list-group-item-action list-group-item-warning"><strong>Manajemen Perpustakaan</strong></a>
                        @endif
                    </div>
                </div>

                {{-- Pengumuman --}}
                <div class="card">
                    <div class="card-header">Pengumuman Terbaru</div>
                    <div class="list-group list-group-flush">
                        @forelse($pengumumans as $p)
                            {{-- PERBAIKAN: Mengarahkan ke rute publik yang benar --}}
                            <a href="{{ route('pengumuman.public.show', $p) }}" class="list-group-item list-group-item-action">
                                <h6 class="mb-1">{{ $p->judul }}</h6>
                                <small class="text-muted">{{ $p->created_at->isoFormat('D MMMM YYYY') }}</small>
                            </a>
                        @empty
                            <div class="list-group-item text-muted">Belum ada pengumuman.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning">Data dosen Anda tidak ditemukan. Silakan hubungi administrator.</div>
    @endif
</div>
@endsection