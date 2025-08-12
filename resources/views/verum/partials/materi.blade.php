<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Materi Perkuliahan</h4>
    @if(Auth::user()->role == 'dosen')
        {{-- Tombol ini sekarang akan membuka modal --}}
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahMateriModal">+ Tambah Materi</button>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if($verum_kela->materi->isEmpty())
    <p class="text-center text-muted"><i>Belum ada materi yang diunggah.</i></p>
@else
    <ul class="list-group">
        @foreach($verum_kela->materi as $item)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $item->judul }}</strong>
                    <p class="mb-1 text-muted">{{ $item->deskripsi }}</p>
                    <small class="text-muted">Diupload: {{ $item->created_at->format('d M Y') }}</small>
                </div>
                <div class="d-flex align-items-center">
                    @if($item->file_path)
                        {{-- Link unduh yang benar mengarah ke storage --}}
                        <a href="{{ Storage::url($item->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary me-2">Unduh</a>
                    @elseif($item->link_url)
                        <a href="{{ $item->link_url }}" target="_blank" class="btn btn-sm btn-outline-info me-2">Buka Link</a>
                    @endif

                    @if(Auth::user()->role == 'dosen')
                    <form action="{{ route('verum.materi.destroy', $item) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
@endif
