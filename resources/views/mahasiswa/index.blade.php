@extends('layouts.app')
@section('content')
    <h1 class="mb-4">Daftar Mahasiswa</h1>

    {{-- Notifikasi --}}
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Oops!</strong> Terdapat beberapa masalah dengan file impor Anda.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Panel Filter & Aksi --}}
    <div class="card mb-4">
        <div class="card-header">Filter & Aksi</div>
        <div class="card-body">
            <div class="row">
                <!-- Form Filter -->
                <div class="col-md-8">
                    <form action="{{ route('mahasiswa.index') }}" method="GET">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input type="text" class="form-control" placeholder="Cari NIM atau Nama..." name="search" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-5">
                                <select class="form-select" name="program_studi_id">
                                    <option value="">Semua Program Studi</option>
                                    @foreach($program_studis as $prodi)
                                        <option value="{{ $prodi->id }}" {{ request('program_studi_id') == $prodi->id ? 'selected' : '' }}>
                                            {{ $prodi->nama_prodi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary w-100" type="submit">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- Tombol Aksi -->
                <div class="col-md-4 d-flex justify-content-end align-items-center">
                     <a href="{{ route('mahasiswa.create') }}" class="btn btn-primary me-2">Tambah</a>
                     <div class="btn-group">
                        <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Ekspor/Impor
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('mahasiswa.export', request()->query()) }}">Ekspor ke Excel</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importModal">Impor dari Excel</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Mahasiswa --}}
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>NIM</th>
                <th>Nama Lengkap</th>
                <th>Program Studi</th>
                <th>Akun Login (Email)</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mahasiswas as $mahasiswa)
                <tr>
                    <td>{{ $mahasiswa->nim }}</td>
                    <td>{{ $mahasiswa->nama_lengkap }}</td>
                    <td>{{ $mahasiswa->programStudi->nama_prodi }}</td>
                    <td>{{ $mahasiswa->user->email ?? 'Belum Ada Akun' }}</td>
                    <td>
                        <a href="{{ route('mahasiswa.edit', $mahasiswa->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('mahasiswa.destroy', $mahasiswa->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus mahasiswa ini beserta akun loginnya?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Data tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Paginasi --}}
    <div class="d-flex justify-content-center">
        {{ $mahasiswas->appends(request()->query())->links() }}
    </div>

    <!-- Modal Impor -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Impor Data Mahasiswa dari Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('mahasiswa.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <p>
                            Unduh template terlebih dahulu untuk memastikan format benar.
                            <a href="{{ route('mahasiswa.import.template') }}" class="fw-bold">Unduh Template Di Sini</a>
                        </p>
                        <hr>
                        <div class="mb-3">
                            <label for="file" class="form-label">Pilih File Excel untuk Diunggah</label>
                            <input class="form-control" type="file" name="file" id="file" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Impor Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
