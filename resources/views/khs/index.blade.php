@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- Header & Aksi --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Kartu Hasil Studi (KHS)</h3>
            <span class="text-muted small uppercase">Rekam Jejak Penilaian Akademik Mahasiswa</span>
        </div>
        <a href="{{ route('khs.cetak') }}" class="btn btn-sm btn-dark rounded-0 px-4 fw-bold uppercase small shadow-sm">
            <i class="bi bi-printer me-2"></i>Cetak KHS (PDF)
        </a>
    </div>

    {{-- Kartu Data Mahasiswa --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
        <div class="card-body p-4 font-monospace">
            <div class="row uppercase small text-dark">
                <div class="col-md-6 border-end">
                    <p class="mb-2"><strong>NIM:</strong> <span class="text-primary fw-bold">{{ $mahasiswa->nim }}</span></p>
                    <p class="mb-0"><strong>NAMA LENGKAP:</strong> {{ $mahasiswa->nama_lengkap }}</p>
                </div>
                <div class="col-md-6 ps-md-4">
                    <p class="mb-2"><strong>PROGRAM STUDI:</strong> {{ optional($mahasiswa->programStudi)->nama_prodi }}</p>
                    <p class="mb-0"><strong>DOSEN WALI:</strong> {{ optional(optional($mahasiswa->dosenWali)->user)->name ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar KHS Per Semester (Diubah Menjadi Accordion) --}}
    @if($krsPerTahunAkademik->isEmpty())
        <div class="alert alert-light border rounded-0 text-center py-5 uppercase small fw-bold text-muted shadow-sm">
            Belum ada nilai yang diinput untuk semester manapun.
        </div>
    @else
        <div class="accordion mb-4 shadow-sm" id="accordionKHS">
            @php $iteration = 0; @endphp
            @foreach ($krsPerTahunAkademik->sortKeysDesc() as $tahunAkademikId => $krs)
                @php
                    $tahun = $tahunAkademiks->find($tahunAkademikId);
                    $ipsData = $mahasiswa->hitungIps($tahunAkademikId);
                    $isFirst = ($iteration === 0); // Buka panel pertama saja secara otomatis
                @endphp
                <div class="accordion-item border-0 rounded-0 mb-2">
                    <h2 class="accordion-header" id="heading-{{ $tahunAkademikId }}">
                        <button class="accordion-button {{ $isFirst ? '' : 'collapsed' }} rounded-0 bg-light text-dark fw-bold uppercase border" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $tahunAkademikId }}" aria-expanded="{{ $isFirst ? 'true' : 'false' }}" aria-controls="collapse-{{ $tahunAkademikId }}">
                            SEMESTER {{ $tahun ? strtoupper($tahun->semester) : '' }} - TAHUN AKADEMIK {{ $tahun ? $tahun->tahun : 'TIDAK DIKETAHUI' }}
                            <span class="badge bg-dark ms-3 font-monospace">IPS: {{ number_format($ipsData['ips'], 2) }}</span>
                        </button>
                    </h2>
                    <div id="collapse-{{ $tahunAkademikId }}" class="accordion-collapse collapse {{ $isFirst ? 'show' : '' }}" aria-labelledby="heading-{{ $tahunAkademikId }}" data-bs-parent="#accordionKHS">
                        <div class="accordion-body p-0 border border-top-0">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0 border-0">
                                    <thead class="table-dark small uppercase text-center">
                                        <tr>
                                            <th style="width: 15%; border-top:0;">KODE MK</th>
                                            <th class="text-start" style="border-top:0;">NAMA MATA KULIAH</th>
                                            <th style="width: 10%; border-top:0;">SKS</th>
                                            <th style="width: 10%; border-top:0;">NILAI</th>
                                            <th style="width: 10%; border-top:0;">BOBOT</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        @foreach ($krs as $mk)
                                            <tr>
                                                <td class="text-center font-monospace fw-bold text-muted">{{ $mk->kode_mk }}</td>
                                                <td class="uppercase fw-bold text-dark">{{ $mk->nama_mk }}</td>
                                                <td class="text-center font-monospace fw-bold">{{ $mk->sks }}</td>
                                                <td class="text-center font-monospace fw-bold text-primary">{{ $mk->pivot->nilai ?? '-' }}</td>
                                                <td class="text-center font-monospace">{{ $mk->pivot->nilai ? ($ipsData['nilaiBobot'][$mk->id] ?? 0) : '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light fw-bold uppercase small text-dark">
                                        <tr>
                                            <td colspan="2" class="text-end py-2 pe-3">TOTAL SKS</td>
                                            <td class="text-center py-2 font-monospace fs-6 text-primary">{{ $ipsData['total_sks'] }}</td>
                                            <td class="text-end py-2 pe-3">IPS</td>
                                            <td class="text-center py-2 font-monospace fs-6 text-primary">{{ number_format($ipsData['ips'], 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @php $iteration++; @endphp
            @endforeach
        </div>
    @endif

    {{-- Ringkasan IPK Akhir --}}
    <div class="card border-0 rounded-0 bg-dark text-white mt-4 mb-5 shadow-sm">
         <div class="card-body p-4">
             <div class="row align-items-center">
                 <div class="col-md-6 text-center text-md-start mb-3 mb-md-0 border-md-end border-secondary">
                     <h6 class="uppercase small opacity-75 mb-1">TOTAL SKS LULUS</h6>
                     <h3 class="fw-bold font-monospace mb-0 text-success">{{ $mahasiswa->totalSksLulus() }} <span class="fs-6 fw-normal text-white">SKS</span></h3>
                 </div>
                 <div class="col-md-6 text-center text-md-end ps-md-4">
                     <h6 class="uppercase small opacity-75 mb-1">INDEKS PRESTASI KUMULATIF (IPK)</h6>
                     <h3 class="fw-bold font-monospace mb-0 text-warning">{{ number_format($mahasiswa->hitungIpk(), 2) }}</h3>
                 </div>
             </div>
         </div>
    </div>
</div>
@endsection