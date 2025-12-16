@extends('layouts.app')

@section('content')
<div class="container">
    @if(isset($dosen))
        {{-- Pesan Sukses Upload --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Dasbor Dosen</h2>
                <p class="lead text-muted">Selamat datang kembali, {{ $dosen->nama_lengkap }}</p>
            </div>
            <div>
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
                                    <small class="text-muted">{{ $mk->sks }} SKS - {{ $mk->mahasiswas_count }} Mahasiswa</small>
                                    
                                    {{-- Indikator Status RPS --}}
                                    <div class="mt-1">
                                        @if($mk->file_rps)
                                            <span class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle"></i> RPS Tersedia</span>
                                            <a href="{{ asset('storage/' . $mk->file_rps) }}" target="_blank" class="text-decoration-none small ms-2">
                                                <i class="bi bi-eye"></i> Lihat
                                            </a>
                                        @else
                                            <span class="badge bg-warning bg-opacity-10 text-warning"><i class="bi bi-exclamation-circle"></i> RPS Belum Ada</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    {{-- Tombol Upload RPS --}}
                                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#uploadRpsModal-{{ $mk->id }}">
                                        <i class="bi bi-upload"></i> RPS
                                    </button>
                                    <a href="{{ route('nilai.show', $mk) }}" class="btn btn-sm btn-outline-primary">Input Nilai</a>
                                </div>
                            </li>

                            {{-- MODAL UPLOAD RPS (Per Mata Kuliah) --}}
                            <div class="modal fade" id="uploadRpsModal-{{ $mk->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('dosen.upload_rps', $mk->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Upload RPS: {{ $mk->nama_mk }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">File RPS (PDF)</label>
                                                    <input type="file" name="file_rps" class="form-control" accept=".pdf" required>
                                                    <div class="form-text">Maksimal ukuran file 5MB. Format .pdf</div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

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
                {{-- Portal Multi-Peran (Tidak Berubah) --}}
                <div class="card mb-4">
                    <div class="card-header fw-bold">Portal Peran Anda</div>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('perwalian.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Perwalian Akademik <span class="badge bg-primary rounded-pill">{{ $jumlahMahasiswaWali }} Mhs</span>
                        </a>
                        
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

                        @if(Auth::user()->hasRole('rektorat'))
                            <a href="{{ route('rektorat.dashboard') }}" class="list-group-item list-group-item-action list-group-item-dark"><strong>Dasbor Pimpinan</strong></a>
                        @endif
                        
                        @if(Auth::user()->hasRole('penjaminan_mutu'))
                            <a href="{{ route('mutu.dashboard') }}" class="list-group-item list-group-item-action list-group-item-secondary"><strong>Dasbor Penjaminan Mutu</strong></a>
                        @endif

                        @if(Auth::user()->hasRole('keuangan'))
                            <a href="{{ route('pembayaran.index') }}" class="list-group-item list-group-item-action list-group-item-success"><strong>Manajemen Pembayaran</strong></a>
                        @endif

                        @if(Auth::user()->hasRole('pustakawan'))
                            <a href="{{ route('perpustakaan.koleksi.index') }}" class="list-group-item list-group-item-action list-group-item-warning"><strong>Manajemen Perpustakaan</strong></a>
                        @endif
                    </div>
                </div>

                {{-- Pengumuman (Tidak Berubah) --}}
                <div class="card">
                    <div class="card-header">Pengumuman Terbaru</div>
                    <div class="list-group list-group-flush">
                        @forelse($pengumumans as $p)
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