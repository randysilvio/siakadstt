<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <div class="bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
            <i class="bi bi-journal-richtext fs-4"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold text-dark">Materi Perkuliahan</h5>
            <small class="text-muted">Unduh bahan ajar dan referensi perkuliahan.</small>
        </div>
    </div>
    {{-- Pastikan tombol hanya muncul untuk Dosen --}}
    @if(Auth::user()->hasRole('dosen'))
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#tambahMateriModal">
            <i class="bi bi-plus-lg me-1"></i> Tambah Materi
        </button>
    @endif
</div>

{{-- Alert Error --}}
@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

{{-- Content List --}}
@if($verum_kela->materi->isEmpty())
    <div class="text-center py-5 border rounded bg-light">
        <i class="bi bi-folder-x fs-1 text-secondary opacity-50 mb-2"></i>
        <p class="text-muted mb-0">Belum ada materi yang diunggah dosen.</p>
    </div>
@else
    <div class="row g-3">
        @foreach($verum_kela->materi as $item)
            <div class="col-md-6">
                <div class="card h-100 border shadow-sm hover-shadow transition-all">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                {{-- Ikon berbeda tergantung tipe konten (File vs Link) --}}
                                <i class="bi {{ $item->link_url ? 'bi-link-45deg' : 'bi-file-earmark-text-fill' }} fs-3 text-primary me-3"></i>
                                <div>
                                    {{-- Escape output judul sudah otomatis oleh Blade --}}
                                    <h6 class="fw-bold mb-0 text-dark">{{ $item->judul }}</h6>
                                    <small class="text-muted">{{ $item->created_at->format('d M Y') }}</small>
                                </div>
                            </div>
                            
                            {{-- Tombol Hapus: Hanya untuk Dosen --}}
                            @if(Auth::user()->hasRole('dosen'))
                                <form action="{{ route('verum.materi.destroy', $item) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini? Data yang dihapus tidak dapat dikembalikan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm text-danger p-0" title="Hapus Materi">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                        
                        {{-- Deskripsi --}}
                        <p class="small text-secondary mb-3 text-truncate-2">
                            {{ $item->deskripsi ?? 'Tidak ada deskripsi.' }}
                        </p>
                        
                        <div class="d-grid">
                            @if($item->file_path)
                                {{-- SECURITY: rel="noopener noreferrer" mencegah tabnabbing attack --}}
                                <a href="{{ Storage::url($item->file_path) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download me-1"></i> Unduh File
                                </a>
                            @elseif($item->link_url)
                                <a href="{{ $item->link_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka Tautan
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<style>
    .hover-shadow:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    .transition-all { transition: all 0.3s ease; }
    .text-truncate-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>