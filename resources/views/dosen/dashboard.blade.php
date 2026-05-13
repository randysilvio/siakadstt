@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    
    {{-- HEADER PROFIL DOSEN --}}
    <div class="card border-0 shadow-sm mb-4 rounded-0 bg-dark text-white">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 uppercase">{{ strtoupper($dosen->nama_lengkap) }}</h3>
                <p class="mb-0 text-white-50 font-monospace small">NIDN: {{ $dosen->nidn }} | STATUS: TENAGA PENDIDIK AKTIF</p>
            </div>
            <div class="text-end d-none d-md-block">
                <span class="badge bg-primary rounded-0 px-3 py-2 uppercase tracking-wider">Periode Akademik Aktif</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 bg-success text-white py-2 px-3 rounded-0 mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- MATA KULIAH YANG DIAMPU --}}
            <div class="card border-0 shadow-sm rounded-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark uppercase small">Beban Pengajaran Semester Berjalan</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 small">IDENTITAS MATA KULIAH</th>
                                    <th class="small text-center">SKS</th>
                                    <th class="small text-center">MAHASISWA</th>
                                    <th class="small text-center">DOKUMEN RPS</th>
                                    <th class="small text-end pe-3">ADMINISTRASI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mata_kuliahs as $mk)
                                    <tr>
                                        <td class="ps-3">
                                            <span class="fw-bold small text-dark d-block">{{ $mk->nama_mk }}</span>
                                            <span class="text-muted small font-monospace">{{ $mk->kode_mk }}</span>
                                        </td>
                                        <td class="text-center fw-bold small">{{ $mk->sks }}</td>
                                        <td class="text-center small">{{ $mk->mahasiswas_count }} Mhs</td>
                                        <td class="text-center">
                                            @if($mk->file_rps)
                                                <a href="{{ asset('storage/' . $mk->file_rps) }}" target="_blank" class="badge bg-success rounded-0 text-decoration-none">TERSEDIA</a>
                                            @else
                                                <span class="badge bg-warning text-dark rounded-0">BELUM ADA</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-light border rounded-0" data-bs-toggle="modal" data-bs-target="#uploadRpsModal-{{ $mk->id }}">UNGGAH RPS</button>
                                                <a href="{{ route('nilai.show', $mk->id) }}" class="btn btn-sm btn-dark rounded-0 px-3">INPUT NILAI</a>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- MODAL UPLOAD RPS --}}
                                    <div class="modal fade" id="uploadRpsModal-{{ $mk->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content rounded-0 border-0">
                                                <form action="{{ route('dosen.upload_rps', $mk->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="modal-header border-bottom">
                                                        <h6 class="modal-title fw-bold uppercase">Update RPS: {{ $mk->nama_mk }}</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <label class="form-label small fw-bold">BERKAS RPS (FORMAT PDF, MAKS. 5MB)</label>
                                                        <input type="file" name="file_rps" class="form-control rounded-0" accept=".pdf" required>
                                                    </div>
                                                    <div class="modal-footer bg-light border-top">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-0" data-bs-dismiss="modal">BATAL</button>
                                                        <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4">SIMPAN PERUBAHAN</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr><td colspan="5" class="text-center py-5 text-muted small uppercase">Tidak terdapat penugasan pengajaran pada periode ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- JADWAL KULIAH MINGGUAN --}}
            <div class="card border-0 shadow-sm rounded-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark uppercase small">Agenda Perkuliahan Mingguan</h6>
                    <a href="{{ route('dosen.cetak_jadwal') }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 px-3 font-bold small">CETAK JADWAL</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 small">HARI</th>
                                <th class="small">WAKTU (WIT)</th>
                                <th class="small">MATA KULIAH</th>
                                <th class="small">RUANG</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($jadwalKuliah as $jadwal)
                                <tr>
                                    <td class="ps-3 fw-bold small text-dark">{{ strtoupper($jadwal->hari) }}</td>
                                    <td class="font-monospace small text-muted">
                                        {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                    </td>
                                    <td class="fw-bold small text-dark">{{ $jadwal->mataKuliah->nama_mk }}</td>
                                    <td class="small text-muted font-monospace">{{ $jadwal->ruangan ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted small uppercase">Jadwal perkuliahan belum diterbitkan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- PORTAL MULTI-PERAN --}}
            <div class="card border-0 shadow-sm rounded-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark uppercase small">Aksesibilitas Peran</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('perwalian.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold small text-dark d-block">BIMBINGAN PERWALIAN</span>
                            <small class="text-muted">Manajemen progres mahasiswa wali</small>
                        </div>
                        <span class="badge bg-dark rounded-0 px-2">{{ $jumlahMahasiswaWali }}</span>
                    </a>
                    
                    @if(Auth::user()->hasRole('kaprodi'))
                        <a href="{{ route('kaprodi.dashboard') }}" class="list-group-item list-group-item-action py-3 border-start border-primary border-4 d-flex justify-content-between align-items-center bg-light">
                            <div>
                                <span class="fw-bold small text-primary d-block">DASHBOARD KETUA PROGRAM STUDI</span>
                                <small class="text-muted">Otoritas validasi akademik</small>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    @endif

                    @if(Auth::user()->hasRole('rektorat'))
                        <a href="{{ route('rektorat.dashboard') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Monitoring Rektorat</span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- WARTA AKADEMIK --}}
            <div class="card border-0 shadow-sm rounded-0">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="fw-bold mb-0 uppercase small">Warta Resmi Institusi</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($pengumumans as $p)
                            <a href="{{ route('pengumuman.public.show', $p->id) }}" class="list-group-item list-group-item-action py-3 border-bottom">
                                <small class="text-muted d-block mb-1 font-monospace">{{ $p->created_at->translatedFormat('d M Y') }}</small>
                                <span class="fw-bold small text-dark leading-tight">{{ Str::limit($p->judul, 60) }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection