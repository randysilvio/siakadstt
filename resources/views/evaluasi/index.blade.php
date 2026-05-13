@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Evaluasi Dosen oleh Mahasiswa (EDOM)</h3>
            <span class="text-muted small uppercase">Periode Akademik: {{ $sesiAktif->nama_sesi }}</span>
        </div>
    </div>

    {{-- NOTIFIKASI VALIDASI KRS --}}
    @if(Auth::user()->mahasiswa->status_krs !== 'Disetujui')
        <div class="alert alert-warning border-0 shadow-sm rounded-0 mb-4 d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <span class="fw-bold d-block uppercase small">Akses Evaluasi Ditangguhkan</span>
                <p class="mb-0 small">Kuesioner hanya dapat diisi apabila Kartu Rencana Studi (KRS) Anda telah mendapatkan status <strong>"DISETUJUI"</strong> oleh Kepala Program Studi.</p>
            </div>
        </div>
    @else
        <div class="alert bg-light border text-dark rounded-0 mb-4 small">
            <i class="bi bi-info-circle-fill text-primary me-2"></i>
            <strong>INSTRUKSI:</strong> Berikan penilaian objektif berdasarkan pengalaman proses belajar mengajar selama satu semester. Partisipasi Anda dijamin kerahasiaannya.
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="fw-bold mb-0 text-dark uppercase small">Daftar Mata Kuliah dalam Rencana Studi</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="ps-4 py-3 text-muted small" style="width: 150px;">KODE MK</th>
                            <th class="text-muted small">IDENTITAS MATA KULIAH</th>
                            <th class="text-muted small">DOSEN PENGAMPU</th>
                            <th class="text-center text-muted small" style="width: 150px;">STATUS</th>
                            <th class="text-end text-muted small pe-4" style="width: 180px;">TINDAKAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mataKuliah as $mk)
                            <tr>
                                <td class="ps-4 font-monospace fw-bold text-dark">{{ $mk->kode_mk }}</td>
                                <td class="fw-bold text-dark small uppercase">{{ $mk->nama_mk }}</td>
                                <td class="text-secondary small">{{ $mk->dosen->nama_lengkap ?? 'BELUM DITETAPKAN' }}</td>
                                <td class="text-center">
                                    @if (in_array($mk->id, $evaluasiSelesai))
                                        <span class="badge bg-success rounded-0 px-2">TERKIRIM</span>
                                    @else
                                        <span class="badge bg-secondary rounded-0 px-2">BELUM DIISI</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @if ($mk->dosen)
                                        @if (in_array($mk->id, $evaluasiSelesai))
                                            <a href="{{ route('evaluasi.show', $mk->id) }}" class="btn btn-sm btn-light border rounded-0 text-dark">Buka Evaluasi</a>
                                        @else
                                            @if(Auth::user()->mahasiswa->status_krs === 'Disetujui')
                                                <a href="{{ route('evaluasi.show', $mk->id) }}" class="btn btn-sm btn-dark rounded-0 px-3">Isi Kuesioner</a>
                                            @else
                                                <button class="btn btn-sm btn-light border rounded-0 opacity-50" disabled>Terkunci</button>
                                            @endif
                                        @endif
                                    @else
                                        <span class="text-muted small italic">Dosen N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted small uppercase">Tidak terdapat data mata kuliah yang dapat dievaluasi pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection