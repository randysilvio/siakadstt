@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- Header & Navigasi Cepat Antrean --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 mt-3 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Validasi Rencana Studi (Kaprodi)</h3>
            <span class="text-muted small uppercase">Tinjau Rencana Studi Mahasiswa & Rekomendasi Tutorship Semester Berjalan</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(isset($prevMahasiswaId) && $prevMahasiswaId)
                <a href="{{ route('kaprodi.krs.show', $prevMahasiswaId) }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 fw-bold uppercase small">
                    <i class="bi bi-chevron-left me-1"></i> Sebelumnya
                </a>
            @endif
            @if(isset($nextMahasiswaId) && $nextMahasiswaId)
                <a href="{{ route('kaprodi.krs.show', $nextMahasiswaId) }}" class="btn btn-sm btn-primary rounded-0 px-3 fw-bold uppercase small shadow-sm">
                    Antrean Berikutnya <i class="bi bi-chevron-right ms-1"></i>
                </a>
            @endif
            <a href="{{ route('kaprodi.dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 fw-bold uppercase small">
                <i class="bi bi-arrow-left me-1"></i> Daftar Mahasiswa
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        {{-- Kartu Profil Mahasiswa --}}
        <div class="col-md-5">
            <div class="card shadow-sm border-0 rounded-0 border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    Identitas & Portofolio Mahasiswa
                </div>
                <div class="card-body p-4">
                    <table class="table table-borderless small uppercase text-dark mb-0">
                        <tr>
                            <td style="width: 150px;" class="fw-bold">NAMA MAHASISWA</td>
                            <td>: <strong>{{ $mahasiswa->nama_lengkap }}</strong></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">NIM / ANGKATAN</td>
                            <td class="font-monospace fw-bold text-primary">: {{ $mahasiswa->nim }} ({{ $mahasiswa->tahun_masuk }})</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">DOSEN TUTOR</td>
                            <td>: {{ optional($mahasiswa->dosenWali)->nama_lengkap ?? 'BELUM DIPLOT' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">IPK / SKS LULUS</td>
                            <td class="font-monospace fw-bold">: {{ number_format($ipk ?? $mahasiswa->hitungIpk(), 2) }} ({{ $sksLulus ?? $mahasiswa->totalSksLulus() }} SKS)</td>
                        </tr>
                        <tr>
                            <td class="fw-bold align-middle">STATUS KRS</td>
                            <td class="align-middle">:
                                @if($mahasiswa->status_krs == 'Disetujui')
                                    <span class="badge bg-success rounded-0 uppercase font-monospace fw-bold px-2 py-1">DISETUJUI</span>
                                @elseif($mahasiswa->status_krs == 'Menunggu Persetujuan')
                                    <span class="badge bg-warning text-dark rounded-0 uppercase font-monospace fw-bold px-2 py-1">MENUNGGU VALIDASI</span>
                                @elseif($mahasiswa->status_krs == 'Ditolak')
                                    <span class="badge bg-danger rounded-0 uppercase font-monospace fw-bold px-2 py-1">DITOLAK / REVISI</span>
                                @else
                                    <span class="badge bg-secondary rounded-0 uppercase font-monospace fw-bold px-2 py-1">{{ $mahasiswa->status_krs ?? 'DRAFT' }}</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Panel Keputusan Validasi Kaprodi (Stay-on-Page) --}}
        <div class="col-md-7">
            <div class="card shadow-sm border-0 rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-header bg-dark text-white py-3 rounded-0 uppercase fw-bold small">
                    Otorisasi & Catatan Ketua Program Studi
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <form action="{{ route('kaprodi.krs.update', $mahasiswa->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label small fw-bold uppercase text-dark mb-1">Catatan Validasi Kaprodi / Dosen Tutor (Opsional)</label>
                            <textarea name="catatan_kaprodi" rows="2" class="form-control rounded-0 small uppercase" placeholder="TULIS CATATAN ATAU ALASAN PENOLAKAN KRS JIKA PERLU REVISI...">{{ old('catatan_kaprodi', $mahasiswa->catatan_kaprodi) }}</textarea>
                        </div>

                        <div class="d-flex flex-wrap gap-2 justify-content-end pt-2 border-top">
                            @if($mahasiswa->status_krs != 'Menunggu Persetujuan')
                                <button type="submit" name="status_krs" value="Menunggu Persetujuan" class="btn btn-outline-dark btn-sm rounded-0 fw-bold uppercase px-3 py-2">
                                    Reset ke Menunggu
                                </button>
                            @endif

                            <button type="submit" name="status_krs" value="Ditolak" class="btn btn-danger btn-sm rounded-0 fw-bold uppercase px-4 py-2 shadow-sm" {{ $mahasiswa->status_krs == 'Ditolak' ? 'disabled' : '' }}>
                                <i class="bi bi-x-lg me-1"></i> Tolak / Minta Revisi
                            </button>

                            <button type="submit" name="status_krs" value="Disetujui" class="btn btn-success btn-sm rounded-0 fw-bold uppercase px-4 py-2 shadow-sm" {{ $mahasiswa->status_krs == 'Disetujui' ? 'disabled' : '' }}>
                                <i class="bi bi-check-lg me-1"></i> Setujui KRS
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Mata Kuliah --}}
    <div class="card shadow-sm border-0 rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 uppercase fw-bold small">Daftar Mata Kuliah yang Diambil</h6>
            <span class="badge bg-light text-dark rounded-0 font-monospace">TOTAL: {{ $mahasiswa->mataKuliahs->sum('sks') }} SKS</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 align-middle">
                    <thead class="bg-light small uppercase fw-bold text-center text-dark">
                        <tr>
                            <th style="width: 6%;">NO</th>
                            <th style="width: 14%;">KODE MK</th>
                            <th class="text-start">MATA KULIAH</th>
                            <th style="width: 8%;">SMT</th>
                            <th style="width: 8%;">SKS</th>
                            <th class="text-start" style="width: 34%;">JADWAL & DOSEN PENGAMPU</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @php $totalSks = 0; @endphp
                        @forelse ($mahasiswa->mataKuliahs as $index => $mk)
                            <tr>
                                <td class="text-center font-monospace text-muted fw-bold">{{ $index + 1 }}</td>
                                <td class="text-center font-monospace fw-bold text-primary">{{ $mk->kode_mk }}</td>
                                <td class="uppercase fw-bold text-dark ps-3">{{ $mk->nama_mk }}</td>
                                <td class="text-center font-monospace">{{ $mk->semester }}</td>
                                <td class="text-center font-monospace fw-bold">{{ $mk->sks }}</td>
                                <td class="ps-3">
                                    <div class="small">
                                        @if(isset($mk->dosen) && $mk->dosen)
                                            <div class="text-dark fw-bold uppercase mb-1" style="font-size: 10px;"><i class="bi bi-person-badge me-1"></i> {{ $mk->dosen->nama_lengkap }}</div>
                                        @endif

                                        @if(isset($mk->jadwals) && $mk->jadwals->isNotEmpty())
                                            <ul class="list-unstyled mb-0 text-muted font-monospace" style="font-size: 10px;">
                                            @foreach($mk->jadwals as $jadwal)
                                                <li><i class="bi bi-calendar-event me-1"></i> {{ strtoupper($jadwal->hari) }}, {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }} WIT ({{ $jadwal->ruang ?? 'TBA' }})</li>
                                            @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted uppercase" style="font-size: 10px;">- JADWAL BELUM DIATUR -</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @php $totalSks += $mk->sks; @endphp
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted small uppercase fw-bold">
                                    Mahasiswa ini belum memilih mata kuliah pada semester aktif.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light fw-bold uppercase small text-dark">
                        <tr>
                            <td colspan="4" class="text-end py-2 pe-3">TOTAL BEBAN SKS DIAMBIL</td>
                            <td class="text-center py-2 text-primary font-monospace fs-6">{{ $totalSks }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection