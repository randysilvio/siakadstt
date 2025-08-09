@extends('layouts.app')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Kartu Hasil Studi (KHS)</h1>
        {{-- TOMBOL CETAK PDF --}}
        <a href="{{ route('khs.cetak') }}" class="btn btn-primary">Cetak KHS (PDF)</a>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">Data Mahasiswa</div>
        <div class="card-body">
            <p><strong>NIM:</strong> {{ $mahasiswa->nim }}</p>
            <p><strong>Nama Lengkap:</strong> {{ $mahasiswa->nama_lengkap }}</p>
        </div>
    </div>

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
            @forelse ($krs as $mk)
                <tr>
                    <td>{{ $mk->kode_mk }}</td>
                    <td>{{ $mk->nama_mk }}</td>
                    <td>{{ $mk->sks }}</td>
                    <td>{{ $mk->pivot->nilai }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Belum ada nilai yang diinput.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-end">Total SKS</th>
                <th colspan="2">{{ $total_sks }}</th>
            </tr>
            <tr>
                <th colspan="2" class="text-end">Indeks Prestasi Semester (IPS)</th>
                <th colspan="2">{{ $ips }}</th>
            </tr>
        </tfoot>
    </table>
@endsection