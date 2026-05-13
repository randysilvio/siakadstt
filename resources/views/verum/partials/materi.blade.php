{{-- HEADER MATERI --}}
<div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-dark border-2 pb-3">
    <div class="d-flex align-items-center">
        <div class="bg-dark text-white rounded-0 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
            <i class="bi bi-journal-richtext fs-5"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold text-dark uppercase">Materi Perkuliahan</h5>
            <span class="text-muted small uppercase font-monospace">Repositori Modul Ajar & Referensi Kepustakaan Digital</span>
        </div>
    </div>
    
    {{-- Tombol Tambah Materi untuk Dosen --}}
    @if(Auth::user()->hasRole('dosen'))
        <button class="btn btn-sm btn-dark rounded-0 px-4 uppercase fw-bold small shadow-sm py-2" data-bs-toggle="modal" data-bs-target="#tambahMateriModal">
            <i class="bi bi-plus-lg me-2 text-white align-middle"></i> Tambah Materi
        </button>
    @endif
</div>

{{-- ALERT ERROR GLOBAL --}}
@if($errors->any())
    <div class="alert alert-danger border rounded-0 p-3 mb-4 font-monospace small uppercase shadow-sm">
        <strong class="d-block mb-1">GAGAL MEMPROSES FILE:</strong>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- GRID DAFTAR MATERI FLAT 0PX --}}
@if($verum_kela->materi->isEmpty())
    <div class="text-center py-5 bg-light border border-dark border-opacity-25 rounded-0 mb-5">
        <i class="bi bi-folder-x fs-2 d-block mb-2 text-dark opacity-25"></i>
        <p class="text-muted small uppercase fw-bold mb-0">Belum ada berkas materi ajar yang didistribusikan pada kelas ini.</p>
    </div>
@else
    <div class="row g-4 mb-5">
        @foreach($verum_kela->materi as $item)
            <div class="col-md-6">
                <div class="card h-100 border border-dark border-opacity-25 rounded-0 shadow-none bg-white">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            {{-- Baris Judul & Tanggal --}}
                            <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-3">
                                <div class="d-flex align-items-start">
                                    <i class="bi {{ $item->link_url ? 'bi-link-45deg' : 'bi-file-earmark-text-fill' }} fs-3 text-dark me-3 mt-1 align-top"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1 text-dark uppercase line-clamp-2" style="line-height: 1.3;">
                                            {{ $item->judul }}
                                        </h6>
                                        <span class="text-muted font-monospace uppercase d-block" style="font-size: 11px;">
                                            DIPUBLIKASIKAN: {{ $item->created_at->format('d/m/Y') }}
                                        </span>
                                    </div>
                                </div>
                                
                                {{-- Eksekusi Hapus Dosen --}}
                                @if(Auth::user()->hasRole('dosen'))
                                    <form action="{{ route('verum.materi.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus materi perkuliahan ini secara permanen dari penyimpanan server?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2 ms-2" title="Hapus Materi">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                            
                            {{-- Uraian Singkat --}}
                            <p class="text-dark small uppercase line-clamp-3 mb-4" style="line-height: 1.6; text-align: justify;">
                                {{ $item->deskripsi ?? 'TIDAK ADA URAIAN PENJELASAN UNTUK BERKAS INI.' }}
                            </p>
                        </div>
                        
                        {{-- Tombol Aksi Akses Dokumen --}}
                        <div class="d-grid pt-2 border-top">
                            @if($item->file_path)
                                <a href="{{ Storage::url($item->file_path) }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary rounded-0 py-2 uppercase fw-bold small shadow-sm text-center">
                                    <i class="bi bi-download me-2 align-middle text-white"></i> Unduh Dokumen
                                </a>
                            @elseif($item->link_url)
                                <a href="{{ $item->link_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-dark rounded-0 py-2 uppercase fw-bold small shadow-sm text-center">
                                    <i class="bi bi-box-arrow-up-right me-2 align-middle text-white"></i> Buka Tautan Eksternal
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
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
</style>