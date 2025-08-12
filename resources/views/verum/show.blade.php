@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Header Kelas --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h2 class="card-title">{{ $verum_kela->nama_kelas }}</h2>
            <p class="card-subtitle mb-2 text-muted">{{ $verum_kela->mataKuliah->nama_matakuliah }}</p>
            <p class="card-text">{{ $verum_kela->deskripsi }}</p>
        </div>
    </div>

    {{-- Navigasi Tab --}}
    <ul class="nav nav-tabs mb-3" id="classTab" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active" id="forum-tab" data-bs-toggle="tab" data-bs-target="#forum" type="button" role="tab" aria-controls="forum" aria-selected="true">Forum</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="materi-tab" data-bs-toggle="tab" data-bs-target="#materi" type="button" role="tab" aria-controls="materi" aria-selected="false">Materi</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tugas-tab" data-bs-toggle="tab" data-bs-target="#tugas" type="button" role="tab" aria-controls="tugas" aria-selected="false">Tugas</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="presensi-tab" data-bs-toggle="tab" data-bs-target="#presensi" type="button" role="tab" aria-controls="presensi" aria-selected="false">Presensi</button></li>
    </ul>

    {{-- Konten dari setiap Tab --}}
    <div class="tab-content" id="classTabContent">
        <div class="tab-pane fade show active" id="forum" role="tabpanel" aria-labelledby="forum-tab">@include('verum.partials.forum')</div>
        <div class="tab-pane fade" id="materi" role="tabpanel" aria-labelledby="materi-tab">@include('verum.partials.materi')</div>
        <div class="tab-pane fade" id="tugas" role="tabpanel" aria-labelledby="tugas-tab">@include('verum.partials.tugas')</div>
        <div class="tab-pane fade" id="presensi" role="tabpanel" aria-labelledby="presensi-tab">
            @include('verum.partials.presensi')
        </div>
    </div>
</div>

{{-- MODAL UNTUK TAMBAH MATERI --}}
@if(Auth::user()->role == 'dosen')
<div class="modal fade" id="tambahMateriModal" tabindex="-1" aria-labelledby="tambahMateriModalLabel" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="tambahMateriModalLabel">Tambah Materi Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <form action="{{ route('verum.materi.store', $verum_kela) }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="modal-body">
              <div class="mb-3"><label for="judul" class="form-label">Judul Materi</label><input type="text" class="form-control" id="judul" name="judul" required></div>
              <div class="mb-3"><label for="deskripsi" class="form-label">Deskripsi (Opsional)</label><textarea class="form-control" id="deskripsi" name="deskripsi" rows="2"></textarea></div>
              <div class="mb-3"><label for="file_materi" class="form-label">Unggah File</label><input class="form-control" type="file" id="file_materi" name="file_materi"></div>
              <div class="text-center my-2">ATAU</div>
              <div class="mb-3"><label for="link_url" class="form-label">Tempelkan Link</label><input type="url" class="form-control" id="link_url" name="link_url" placeholder="https://..."></div>
          </div>
          <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan Materi</button></div>
      </form>
  </div></div>
</div>

{{-- MODAL UNTUK TAMBAH TUGAS --}}
<div class="modal fade" id="tambahTugasModal" tabindex="-1" aria-labelledby="tambahTugasModalLabel" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="tambahTugasModalLabel">Buat Tugas Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <form action="{{ route('verum.tugas.store', $verum_kela) }}" method="POST">
          @csrf
          <div class="modal-body">
              <div class="mb-3"><label for="judul_tugas" class="form-label">Judul Tugas</label><input type="text" class="form-control" id="judul_tugas" name="judul" required></div>
              <div class="mb-3"><label for="instruksi" class="form-label">Instruksi</label><textarea class="form-control" id="instruksi" name="instruksi" rows="4" required></textarea></div>
              <div class="mb-3"><label for="tenggat_waktu" class="form-label">Tenggat Waktu</label><input type="datetime-local" class="form-control" id="tenggat_waktu" name="tenggat_waktu" required></div>
          </div>
          <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan Tugas</button></div>
      </form>
  </div></div>
</div>

{{-- MODAL UNTUK BUKA SESI PRESENSI --}}
<div class="modal fade" id="bukaPresensiModal" tabindex="-1" aria-labelledby="bukaPresensiModalLabel" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="bukaPresensiModalLabel">Buka Sesi Presensi Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <form action="{{ route('verum.presensi.store', $verum_kela) }}" method="POST">
          @csrf
          <div class="modal-body">
              <div class="mb-3"><label for="judul_pertemuan" class="form-label">Judul Pertemuan</label><input type="text" class="form-control" id="judul_pertemuan" name="judul_pertemuan" placeholder="Contoh: Pembahasan Doktrin Allah" required></div>
              <div class="mb-3"><label for="pertemuan_ke" class="form-label">Pertemuan Ke-</label><input type="number" class="form-control" id="pertemuan_ke" name="pertemuan_ke" min="1" required></div>
              <div class="mb-3"><label for="durasi" class="form-label">Durasi Sesi (menit)</label><input type="number" class="form-control" id="durasi" name="durasi" min="1" value="15" required></div>
          </div>
          <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Buka Sesi</button></div>
      </form>
  </div></div>
</div>
@endif
@endsection
