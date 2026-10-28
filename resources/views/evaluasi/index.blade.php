@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Evaluasi Dosen oleh Mahasiswa (EDOM) <x-info-aturan kunci="edom" /></h3>
            <span class="text-muted small uppercase">Periode Akademik: {{ $sesiAktif->nama_sesi }}</span>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    {{-- [PERBAIKAN UX FASE 3]: Menampilkan Alert Sukses & Error Interceptor KHS/Transkrip --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- NOTIFIKASI VALIDASI KRS --}}
    @if(Auth::user()->mahasiswa->status_krs !== 'Disetujui')
        <div class="alert alert-warning border rounded-0 shadow-sm mb-4 p-3 d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <span class="fw-bold d-block uppercase small">Akses Evaluasi Ditangguhkan</span>
                <p class="mb-0 small">Kuesioner hanya dapat diisi apabila Kartu Rencana Studi (KRS) Anda telah mendapatkan status <strong>"DISETUJUI"</strong> oleh Dosen Wali / Kaprodi.</p>
            </div>
        </div>
    @else
        <div class="alert bg-light border rounded-0 mb-4 p-3 small text-dark shadow-sm">
            <i class="bi bi-info-circle-fill text-primary me-2"></i>
            <strong>INSTRUKSI:</strong> Berikan penilaian objektif berdasarkan pengalaman proses belajar mengajar selama satu semester. Penyelesaian seluruh kuesioner di bawah ini menjadi syarat pembuka akses KHS & Transkrip Nilai.
        </div>
    @endif

    {{-- TABEL STANDAR ENTERPRISE --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold uppercase small">Daftar Mata Kuliah dalam Rencana Studi</span>
            <span class="badge bg-light text-dark rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 11px;">
                TERISI: {{ count($evaluasiSelesai) }} / {{ $mataKuliah->count() }} MK
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 15%;">KODE MK</th>
                            <th class="text-start">IDENTITAS MATA KULIAH</th>
                            <th class="text-start" style="width: 28%;">DOSEN PENGAMPU</th>
                            <th style="width: 15%;">STATUS</th>
                            <th style="width: 18%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse ($mataKuliah as $mk)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-primary">{{ $mk->kode_mk }}</td>
                                <td class="text-start ps-3 fw-bold text-dark uppercase">{{ $mk->nama_mk }}</td>
                                <td class="text-start ps-3 uppercase text-muted fw-bold">{{ $mk->dosen->nama_lengkap ?? 'BELUM DITETAPKAN' }}</td>
                                <td class="text-center">
                                    @if (in_array($mk->id, $evaluasiSelesai))
                                        <span class="badge bg-success text-white rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 10px;">TERKIRIM</span>
                                    @else
                                        <span class="badge bg-secondary text-white rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 10px;">BELUM DIISI</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($mk->dosen)
                                        @if (in_array($mk->id, $evaluasiSelesai))
                                            <a href="{{ route('evaluasi.show', $mk->id) }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                                                <i class="bi bi-pencil-square me-1"></i> Lihat / Ubah
                                            </a>
                                        @else
                                            @if(Auth::user()->mahasiswa->status_krs === 'Disetujui')
                                                <a href="{{ route('evaluasi.show', $mk->id) }}" class="btn btn-sm btn-dark rounded-0 px-3 uppercase fw-bold small">
                                                    Isi Kuesioner <i class="bi bi-arrow-right-short ms-1"></i>
                                                </a>
                                            @else
                                                <button class="btn btn-sm btn-light border rounded-0 opacity-50 uppercase fw-bold small" disabled>Terkunci</button>
                                            @endif
                                        @endif
                                    @else
                                        <span class="text-muted small font-monospace uppercase">DOSEN N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted small uppercase fw-bold">
                                    <i class="bi bi-journal-x fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    Tidak terdapat data mata kuliah yang dapat dievaluasi pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection