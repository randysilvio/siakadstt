@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.evaluasi-hasil.index', ['sesi_id' => $sesi->id]) }}" class="text-decoration-none btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        {{-- TOMBOL CETAK PDF --}}
        <a href="{{ route('admin.evaluasi-hasil.cetak', ['sesi' => $sesi->id, 'dosen' => $dosen->id]) }}" target="_blank" class="btn btn-dark btn-sm">
            <i class="bi bi-printer-fill me-1"></i> Cetak Laporan
        </a>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h1 class="mb-0">{{ $dosen->nama_lengkap }}</h1>
            <p class="text-muted mb-0">NIDN: {{ $dosen->nidn }} | Sesi: {{ $sesi->nama_sesi }}</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="card bg-light border-0">
                <div class="card-body text-center py-2">
                    <small class="text-muted d-block">Total Rata-rata (Skala 4)</small>
                    {{-- Ubah logika warna karena max sekarang 4 --}}
                    <span class="display-6 fw-bold {{ $totalRataRata >= 3.5 ? 'text-success' : ($totalRataRata >= 2.5 ? 'text-warning' : 'text-danger') }}">
                        {{ number_format($totalRataRata, 2) }}
                    </span>
                    <span class="text-muted fs-5">/ 4.00</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Kolom Kiri: Detail Skor Per Pertanyaan --}}
        <div class="col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-header fw-bold">
                    Rincian Penilaian (Skala 1-4)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                @foreach ($detailPerPertanyaan as $item)
                                    <tr>
                                        <td style="width: 50px;" class="align-middle fw-bold text-muted">#{{ $item->urutan }}</td>
                                        <td class="align-middle">{{ $item->pertanyaan }}</td>
                                        <td style="width: 150px;" class="align-middle">
                                            {{-- PERUBAHAN: Dibagi 4 dikali 100 --}}
                                            @php $persen = ($item->skor_rata_rata / 4) * 100; @endphp
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                    <div class="progress-bar {{ $item->skor_rata_rata >= 3 ? 'bg-success' : ($item->skor_rata_rata >= 2 ? 'bg-warning' : 'bg-danger') }}" 
                                                         role="progressbar" style="width: {{ $persen }}%"></div>
                                                </div>
                                                <span class="fw-bold">{{ number_format($item->skor_rata_rata, 2) }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                    @if(!$loop->last) <tr><td colspan="3"><hr class="my-0 text-muted"></td></tr> @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Masukan Teks / Saran --}}
        <div class="col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header fw-bold">
                    Masukan & Saran Mahasiswa
                </div>
                <div class="card-body bg-light overflow-auto" style="max-height: 600px;">
                    @forelse ($masukanTeks as $masukan)
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-body">
                                <p class="mb-1 text-dark fst-italic">"{{ $masukan->jawaban_teks }}"</p>
                                <small class="text-muted" style="font-size: 0.75rem;">
                                    {{ $masukan->created_at->format('d M Y, H:i') }}
                                </small>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-muted my-5">Tidak ada masukan teks.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection