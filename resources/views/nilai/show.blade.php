@extends('layouts.app')
@section('content')
    <h1 class="mb-4">Input Nilai untuk: {{ $mataKuliah->nama_mk }}</h1>

    {{-- Menampilkan pesan error jika tidak ada semester aktif --}}
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('nilai.store') }}" method="POST">
        @csrf
        <input type="hidden" name="mata_kuliah_id" value="{{ $mataKuliah->id }}">
        <div class="table-responsive">
            {{-- Menambahkan class 'align-middle' agar konten sel tabel rata tengah secara vertikal --}}
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Input Nilai (A, B, C, D, E)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mataKuliah->mahasiswas as $mahasiswa)
                        <tr>
                            <td>{{ $mahasiswa->nim }}</td>
                            <td>{{ $mahasiswa->nama_lengkap }}</td>
                            <td>
                                {{-- ======================================================= --}}
                                {{-- ===== PERUBAHAN UTAMA: MENAMBAHKAN KOTAK DISPLAY ===== --}}
                                {{-- ======================================================= --}}
                                <div class="input-group">
                                    {{-- Kotak input tetap ada untuk memasukkan atau mengubah nilai --}}
                                    <input type="text" name="nilai[{{ $mahasiswa->id }}]" class="form-control" 
                                           value="{{ $mahasiswa->pivot->nilai ?? '' }}" placeholder="Input nilai...">
                                    
                                    {{-- Kotak display ini hanya akan muncul jika nilai sudah ada --}}
                                    @if($mahasiswa->pivot->nilai)
                                        <span class="input-group-text bg-success text-white">
                                            Tersimpan: <strong>{{ $mahasiswa->pivot->nilai }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Belum ada mahasiswa yang mengambil mata kuliah ini pada semester aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <button type="submit" class="btn btn-primary">Simpan Nilai</button>

        {{-- Tombol Kembali yang dinamis sesuai peran pengguna --}}
        @if(Auth::user()->hasRole('admin'))
            <a href="{{ route('nilai.index') }}" class="btn btn-secondary">Kembali</a>
        @else
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Kembali ke Dashboard</a>
        @endif
    </form>
@endsection
