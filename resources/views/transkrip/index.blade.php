@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Transkrip Nilai Akademik</h1>
        {{-- TOMBOL CETAK PDF BARU --}}
        <a href="{{ route('transkrip.cetak') }}" class="btn btn-primary">Cetak Transkrip (PDF)</a>
    </div>

    <div class="card mb-4">
        <div class="card-header">Data Mahasiswa</div>
        <div class="card-body">
            <p><strong>NIM:</strong> {{ $mahasiswa->nim }}</p>
            <p><strong>Nama Lengkap:</strong> {{ $mahasiswa->nama_lengkap }}</p>
            <p><strong>Program Studi:</strong> {{ $mahasiswa->programStudi->nama_prodi }}</p>
        </div>
    </div>

    @foreach ($krs_per_semester as $semester => $matkuls)
        <h4 class="mt-4">Semester {{ $semester }}</h4>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Kode MK</th>
                    <th>Nama Mata Kuliah</th>
                    <th>SKS</th>
                    <th>Nilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($matkuls as $mk)
                    <tr>
                        <td>{{ $mk->kode_mk }}</td>
                        <td>{{ $mk->nama_mk }}</td>
                        <td>{{ $mk->sks }}</td>
                        <td>{{ $mk->pivot->nilai }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <div class="card mt-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>Total SKS Ditempuh:</strong> {{ $total_sks }}
                </div>
                <div class="col-md-6">
                    <strong>Indeks Prestasi Kumulatif (IPK):</strong> {{ $ipk }}
                </div>
            </div>
        </div>
    </div>
@endsection