@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- Header & Aksi --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Kartu Hasil Studi (KHS)</h3>
            <span class="text-muted small uppercase">Rapor Akademik Per Semester</span>
        </div>
        
        {{-- Form Filter Semester --}}
        <form action="{{ route('khs.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <select name="tahun_akademik_id" class="form-select form-select-sm rounded-0 uppercase fw-bold" onchange="this.form.submit()" style="min-width: 250px;">
                @if($riwayatTahunAkademiks->isEmpty())
                    <option value="">-- BELUM ADA DATA --</option>
                @else
                    @foreach($riwayatTahunAkademiks as $ta)
                        <option value="{{ $ta->id }}" {{ $selectedTaId == $ta->id ? 'selected' : '' }}>
                            SEMESTER {{ strtoupper($ta->semester) }} {{ $ta->tahun }}
                        </option>
                    @endforeach
                @endif
            </select>
            
            @if($selectedTaId)
                {{-- [PERBAIKAN] Tombol kini membuka Modal Pratinjau, bukan tab baru --}}
                <button type="button" class="btn btn-sm btn-dark rounded-0 px-3 fw-bold uppercase shadow-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#previewPdfModal">
                    <i class="bi bi-file-earmark-pdf me-2"></i>Lihat PDF
                </button>
            @endif
        </form>
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

    {{-- Tabel KHS --}}
    @if(!$tahunSelected || $krsSelected->isEmpty())
        <div class="alert alert-light border rounded-0 text-center py-5 uppercase small fw-bold text-muted shadow-sm">
            <i class="bi bi-folder2-open fs-1 d-block mb-2 text-secondary opacity-50"></i>
            Tidak ada data hasil studi untuk periode yang dipilih.
        </div>
    @else
        <div class="card mb-4 border-0 shadow-sm rounded-0">
            <div class="card-header bg-dark text-white border-bottom rounded-0 py-3">
                <h6 class="mb-0 fw-bold uppercase small text-center">
                    LAPORAN HASIL STUDI SEMESTER {{ strtoupper($tahunSelected->semester) }} - T.A. {{ $tahunSelected->tahun }}
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 border-0">
                        <thead class="table-light small uppercase text-center text-dark">
                            <tr>
                                <th style="width: 15%; border-top:0;">KODE MK</th>
                                <th class="text-start" style="border-top:0;">NAMA MATA KULIAH</th>
                                <th style="width: 10%; border-top:0;">SKS</th>
                                <th style="width: 10%; border-top:0;">NILAI HURUF</th>
                                <th style="width: 10%; border-top:0;">NILAI ANGKA</th>
                            </tr>
                        </thead>
                        <tbody class="small text-dark">
                            @foreach ($krsSelected as $mk)
                                <tr>
                                    <td class="text-center font-monospace fw-bold text-muted">{{ $mk->kode_mk }}</td>
                                    <td class="uppercase fw-bold">{{ $mk->nama_mk }}</td>
                                    <td class="text-center font-monospace fw-bold">{{ $mk->sks }}</td>
                                    <td class="text-center font-monospace fw-bold text-primary">{{ $mk->pivot->nilai ?? '-' }}</td>
                                    <td class="text-center font-monospace">{{ $mk->pivot->nilai ? number_format($ipsData['nilaiBobot'][$mk->id] ?? 0, 2) : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold uppercase small text-dark">
                            <tr>
                                <td colspan="2" class="text-end py-3 pe-3">TOTAL SKS DIAMBIL</td>
                                <td class="text-center py-3 font-monospace fs-6 text-primary">{{ $ipsData['total_sks'] }}</td>
                                <td class="text-end py-3 pe-3">INDEKS PRESTASI SEMESTER (IPS)</td>
                                <td class="text-center py-3 font-monospace fs-6 text-primary">{{ number_format($ipsData['ips'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Ringkasan IPK Akhir --}}
    <div class="card border-0 rounded-0 bg-dark text-white mt-4 mb-5 shadow-sm">
         <div class="card-body p-4">
             <div class="row align-items-center">
                 <div class="col-md-6 text-center text-md-start mb-3 mb-md-0 border-md-end border-secondary">
                     <h6 class="uppercase small opacity-75 mb-1">TOTAL SKS KUMULATIF LULUS</h6>
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

{{-- [TAMBAHAN] Modal Layar Penuh untuk Pratinjau PDF --}}
@if($selectedTaId)
<div class="modal fade" id="previewPdfModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-0 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white rounded-0 py-3">
                <h6 class="modal-title fw-bold uppercase small">
                    <i class="bi bi-file-earmark-pdf me-2"></i>Pratinjau Kartu Hasil Studi
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            {{-- Menggunakan Iframe untuk merender PDF bawaan browser --}}
            <div class="modal-body p-0 bg-secondary" style="height: 75vh;">
                <iframe src="{{ route('khs.cetak', ['tahun_akademik_id' => $selectedTaId]) }}" width="100%" height="100%" style="border: none;"></iframe>
            </div>
            
            <div class="modal-footer bg-light rounded-0">
                <button type="button" class="btn btn-outline-dark rounded-0 px-4 fw-bold uppercase small" data-bs-dismiss="modal">Tutup</button>
                
                {{-- Tombol untuk Unduh Paksa (menyertakan parameter download=1) --}}
                <a href="{{ route('khs.cetak', ['tahun_akademik_id' => $selectedTaId, 'download' => 1]) }}" class="btn btn-primary rounded-0 px-4 fw-bold uppercase small shadow-sm d-flex align-items-center">
                    <i class="bi bi-download me-2"></i>Unduh Berkas
                </a>
            </div>
        </div>
    </div>
</div>
@endif
@endsection