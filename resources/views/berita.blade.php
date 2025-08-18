@extends('layouts.app') {{-- Atau layout publik jika ada --}}

@section('content')
<div class="container py-8">
    <h1 class="text-3xl font-bold mb-8">Semua Berita & Pengumuman</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($semuaBerita as $berita)
            {{-- Tampilkan kartu berita seperti di halaman welcome --}}
        @endforeach
    </div>
    <div class="mt-8">
        {{ $semuaBerita->links() }}
    </div>
</div>
@endsection