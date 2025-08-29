@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="card shadow-lg">
        <div class="card-body p-5">
            <div class="row">
                <div class="col-lg-4 text-center">
                    <img src="{{ $dosen->foto_profil_url }}" class="rounded-circle img-fluid mb-4 border border-3" alt="Foto {{ $dosen->nama_lengkap }}" style="width: 200px; height: 200px; object-fit: cover;">
                    <h2 class="h4">{{ $dosen->nama_lengkap }}</h2>
                    <p class="text-muted">{{ $dosen->jabatan_akademik ?? 'Dosen' }}</p>

                    @if($dosen->email_institusi)
                        <p><a href="mailto:{{ $dosen->email_institusi }}" class="btn btn-sm btn-outline-secondary w-100 mb-2">{{ $dosen->email_institusi }}</a></p>
                    @endif

                    <div class="d-flex justify-content-center">
                        @if($dosen->link_google_scholar)
                            <a href="{{ $dosen->link_google_scholar }}" target="_blank" class="btn btn-sm btn-light me-2">Google Scholar</a>
                        @endif
                        @if($dosen->link_sinta)
                            <a href="{{ $dosen->link_sinta }}" target="_blank" class="btn btn-sm btn-light">SINTA</a>
                        @endif
                    </div>
                </div>

                <div class="col-lg-8 border-start-lg mt-4 mt-lg-0">
                    <h4>Tentang Dosen</h4>
                    <p class="text-muted">{{ $dosen->deskripsi_diri ?? 'Informasi tentang dosen belum tersedia.' }}</p>

                    @if($dosen->bidang_keahlian)
                        <h4 class="mt-4">Bidang Keahlian</h4>
                        <div>
                            @foreach(explode(',', $dosen->bidang_keahlian) as $keahlian)
                                <span class="badge bg-primary fs-6 me-1 mb-1">{{ trim($keahlian) }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if($dosen->mataKuliahs->isNotEmpty())
                         <h4 class="mt-4">Mata Kuliah yang Diampu</h4>
                         <ul class="list-group list-group-flush">
                            @foreach($dosen->mataKuliahs->unique('nama_mk') as $matkul)
                                <li class="list-group-item">{{ $matkul->nama_mk }}</li>
                            @endforeach
                         </ul>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-footer bg-light text-end">
            <a href="{{ route('dosen.public.index') }}" class="btn btn-secondary">Kembali ke Direktori</a>
        </div>
    </div>
</div>
@endsection