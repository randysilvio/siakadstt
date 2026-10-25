@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    
    {{-- PROFIL MAHASISWA --}}
    <div class="card border-0 shadow-sm mb-4 rounded-0 bg-white border-top border-dark border-4">
        <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center gap-3">
            <div>
                @if($mahasiswa->foto_profil)
                    <img src="{{ asset('storage/' . $mahasiswa->foto_profil) }}" class="rounded-0 border border-dark" style="width: 80px; height: 100px; object-fit: cover;">
                @else
                    <div class="bg-light text-secondary border border-dark d-flex align-items-center justify-content-center" style="width: 80px; height: 100px;">
                        <i class="bi bi-person-fill fs-1"></i>
                    </div>
                @endif
            </div>
            <div class="flex-grow-1">
                <h4 class="fw-bold text-dark mb-1 uppercase">{{ $mahasiswa->nama_lengkap }}</h4>
                <p class="text-muted small mb-0 font-monospace uppercase">NIM: {{ $mahasiswa->nim }} | PRODI: {{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }} | ANGKATAN: {{ $mahasiswa->tahun_masuk }}</p>
                <div class="mt-2 d-flex flex-wrap gap-1">
                    <span class="badge bg-dark text-white rounded-0 px-2 py-1 font-monospace">STATUS: {{ strtoupper($mahasiswa->status_mahasiswa) }}</span>
                    <span class="badge bg-light text-dark border rounded-0 px-2 py-1 font-monospace">{{ strtoupper(\App\Models\Pengaturan::get('istilah_pembimbing', 'Dosen Tutor')) }}: {{ strtoupper(optional($mahasiswa->dosenWali)->nama_lengkap ?? 'BELUM DIPLOT') }}</span>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($mahasiswa->status_mahasiswa === 'Aktif')
        @php
            $periodeAktif = \App\Models\TahunAkademik::where('is_active', true)->first();
            $krsSemesterIni = $periodeAktif ? $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->exists() : false;
        @endphp
        <div class="row g-2 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-white h-100">
                    <div class="card-body p-3">
                        <span class="text-muted small font-monospace uppercase fw-bold d-block">ADMINISTRASI KEUANGAN</span>
                        <span class="fw-bold font-monospace small uppercase {{ !$memiliki_tagihan ? 'text-dark' : 'text-danger' }}">
                            {{ !$memiliki_tagihan ? 'LUNAS / AKTIF' : 'ADA TAGIHAN' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-white h-100">
                    <div class="card-body p-3">
                        <span class="text-muted small font-monospace uppercase fw-bold d-block">STATUS KRS</span>
                        <span class="fw-bold font-monospace small uppercase text-dark">
                            {{ $krsSemesterIni ? strtoupper($mahasiswa->status_krs) : 'BELUM MENGAJUKAN' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-white h-100">
                    <div class="card-body p-3">
                        <span class="text-muted small font-monospace uppercase fw-bold d-block">INDEKS PRESTASI (IPK)</span>
                        <span class="fw-bold font-monospace fs-5 text-dark">{{ number_format($ipk, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-white h-100">
                    <div class="card-body p-3">
                        <span class="text-muted small font-monospace uppercase fw-bold d-block">TOTAL SKS LULUS</span>
                        <span class="fw-bold font-monospace fs-5 text-dark">{{ $total_sks }} SKS</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($mahasiswa->status_mahasiswa === 'Lulus')
        <div class="alert bg-white border border-dark rounded-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 p-3 gap-3">
            <div>
                <span class="fw-bold d-block uppercase small text-dark">Status Akademik: Lulus / Alumni</span>
                <small class="text-muted">Silakan lengkapi data Tracer Study untuk mengakses unduhan Transkrip Nilai Akhir.</small>
            </div>
            <a href="{{ route('tracer.index') }}" class="btn btn-sm btn-dark rounded-0 px-4 py-2 uppercase fw-bold small text-nowrap">
                Tracer Study Alumni
            </a>
        </div>
    @elseif($memiliki_tagihan)
        <div class="alert alert-danger border-0 border-start border-4 border-danger shadow-sm rounded-0 d-flex align-items-center mb-4 p-3">
            <div>
                <span class="fw-bold d-block uppercase small">Tagihan Keuangan Belum Lunas</span>
                <small>Silakan unggah bukti pembayaran melalui menu <a href="{{ route('pembayaran.riwayat') }}" class="fw-bold text-danger text-decoration-underline">Riwayat Pembayaran</a>.</small>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                @if($mahasiswa->status_mahasiswa === 'Lulus')
                    <div class="card-header bg-white py-3 border-bottom rounded-0">
                        <h6 class="fw-bold mb-0 text-dark uppercase small">Arsip Akademik</h6>
                    </div>
                    <div class="card-body py-5 text-center">
                        <h6 class="fw-bold text-dark uppercase">Masa Perkuliahan Selesai</h6>
                        <p class="text-muted small mb-3">Seluruh riwayat nilai dapat dilihat pada halaman Transkrip Nilai.</p>
                        <a href="{{ route('transkrip.index') }}" class="btn btn-sm btn-dark rounded-0 px-4 py-2 uppercase fw-bold small">
                            Buka Transkrip Nilai
                        </a>
                    </div>
                @else
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center rounded-0">
                        <h6 class="fw-bold mb-0 text-dark uppercase small">Jadwal Kuliah Semester Ini</h6>
                        <a href="{{ route('jadwal.cetak') }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 px-3 fw-bold uppercase small">
                            Cetak Jadwal
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered align-middle mb-0 small">
                            <thead class="bg-light text-dark uppercase text-center fw-bold">
                                <tr>
                                    <th style="width: 22%;">HARI & WAKTU</th>
                                    <th class="text-start">MATA KULIAH & DOSEN</th>
                                    <th style="width: 10%;">KELAS</th>
                                    <th style="width: 14%;">RUANG</th>
                                    <th style="width: 12%;">RPS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jadwalKuliah as $j)
                                    <tr>
                                        <td class="text-center">
                                            <span class="fw-bold text-dark d-block uppercase">{{ $j->hari }}</span>
                                            <span class="text-muted font-monospace" style="font-size: 11px;">
                                                {{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }}
                                            </span>
                                        </td>
                                        <td class="ps-3">
                                            <span class="fw-bold text-dark uppercase d-block">{{ $j->mataKuliah->nama_mk }} ({{ $j->mataKuliah->sks }} SKS)</span>
                                            <span class="text-muted uppercase" style="font-size: 11px;">{{ optional($j->mataKuliah->dosen)->nama_lengkap ?? '-' }}</span>
                                        </td>
                                        <td class="text-center font-monospace fw-bold">{{ $j->nama_kelas ?? 'A' }}</td>
                                        <td class="text-center font-monospace">{{ $j->ruang ?? '-' }}</td>
                                        <td class="text-center">
                                            @if($j->mataKuliah->file_rps)
                                                <a href="{{ asset('storage/' . $j->mataKuliah->file_rps) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 py-0 px-2 font-monospace" style="font-size: 10px;">PDF</a>
                                            @else
                                                <span class="text-muted font-monospace">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-5 text-muted small uppercase fw-bold">Belum ada jadwal kuliah pada semester ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
                <div class="card-header bg-dark text-white py-3 rounded-0">
                    <h6 class="fw-bold mb-0 uppercase small">Menu Akademik</h6>
                </div>
                <div class="list-group list-group-flush">
                    @if($mahasiswa->status_mahasiswa === 'Lulus')
                        <a href="{{ route('tracer.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Tracer Study Alumni</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('transkrip.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Transkrip Nilai Akhir</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('pembayaran.riwayat') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Riwayat Pembayaran</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    @else
                        <a href="{{ route('krs.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Kartu Rencana Studi (KRS)</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('khs.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Kartu Hasil Studi (KHS)</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('transkrip.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Transkrip Nilai</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('bimbingan.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Bimbingan Tugas Akhir & PPL</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('surat-mandiri.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Permohonan Surat</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('evaluasi.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Evaluasi Dosen</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-dark text-white py-3 rounded-0">
                    <h6 class="fw-bold mb-0 uppercase small">Pengumuman</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($pengumumans as $p)
                            <div class="list-group-item py-3 border-bottom">
                                <small class="text-muted d-block font-monospace" style="font-size: 10px;">{{ $p->created_at->format('d/m/Y') }}</small>
                                <span class="fw-bold small text-dark uppercase d-block">{{ $p->judul }}</span>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small uppercase fw-bold">Belum ada pengumuman.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection