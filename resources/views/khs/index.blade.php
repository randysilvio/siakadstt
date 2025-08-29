@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Kartu Hasil Studi (KHS)</h1>
        <a href="{{ route('khs.cetak') }}" class="btn btn-primary">
            <i class="fas fa-print me-2"></i>Cetak KHS (PDF)
        </a>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Data Mahasiswa</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>NIM:</strong> {{ $mahasiswa->nim }}</p>
                    <p class="mb-0"><strong>Nama Lengkap:</strong> {{ $mahasiswa->nama_lengkap }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Program Studi:</strong> {{ optional($mahasiswa->programStudi)->nama_prodi }}</p>
                    <p class="mb-0"><strong>Dosen Wali:</strong> {{ optional(optional($mahasiswa->dosenWali)->user)->name ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    @forelse ($krsPerTahunAkademik as $tahunAkademikId => $krs)
        @php
            $tahun = $tahunAkademiks->find($tahunAkademikId);
            $ipsData = $mahasiswa->hitungIps($tahunAkademikId);
        @endphp
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Semester {{ $tahun ? $tahun->semester : '' }} - Tahun Akademik {{ $tahun ? $tahun->tahun : 'Tidak Diketahui' }}</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 15%;">Kode MK</th>
                            <th>Nama Mata Kuliah</th>
                            <th style="width: 10%;">SKS</th>
                            <th style="width: 10%;">Nilai</th>
                            <th style="width: 10%;">Bobot</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($krs as $mk)
                            <tr>
                                <td>{{ $mk->kode_mk }}</td>
                                <td>{{ $mk->nama_mk }}</td>
                                <td class="text-center">{{ $mk->sks }}</td>
                                <td class="text-center">{{ $mk->pivot->nilai }}</td>
                                <td class="text-center">{{ $ipsData['nilaiBobot'][$mk->id] ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="2" class="text-end">Total SKS</td>
                            <td class="text-center">{{ $ipsData['total_sks'] }}</td>
                            <td class="text-end">IPS</td>
                            <td class="text-center">{{ number_format($ipsData['ips'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @empty
        <div class="alert alert-info text-center">
            Belum ada nilai yang diinput untuk semester manapun.
        </div>
    @endforelse

    <div class="card mt-4">
         <div class="card-body bg-light">
             <div class="row">
                 <div class="col-md-6">
                     <h4>Total SKS Lulus: <span class="badge bg-success">{{ $mahasiswa->totalSksLulus() }}</span></h4>
                 </div>
                 <div class="col-md-6">
                     <h4>Indeks Prestasi Kumulatif (IPK): <span class="badge bg-primary">{{ number_format($mahasiswa->hitungIpk(), 2) }}</span></h4>
                 </div>
             </div>
         </div>
    </div>

</div>
@endsection
