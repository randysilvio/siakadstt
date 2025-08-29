<h4>Forum Diskusi</h4>

{{-- Form untuk membuat postingan baru --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('verum.forum.store', $verum_kela) }}" method="POST">
            @csrf
            <div class="mb-3">
                <textarea class="form-control @error('konten') is-invalid @enderror" name="konten" rows="3" placeholder="Mulai diskusi, ajukan pertanyaan..."></textarea>
                @error('konten')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Kirim Postingan</button>
        </form>
    </div>
</div>

{{-- Daftar postingan yang sudah ada --}}
@if($verum_kela->postingan->isEmpty())
    <p class="text-center text-muted"><i>Belum ada postingan di forum ini. Jadilah yang pertama memulai diskusi!</i></p>
@else
    @foreach($verum_kela->postingan as $post)
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <div style="width: 40px; height: 40px; background-color: #0d6efd; color: white; border-radius: 50%;" class="d-flex align-items-center justify-content-center fw-bold">
                            {{-- PERBAIKAN: Gunakan optional() untuk keamanan jika user tidak ada --}}
                            {{ strtoupper(substr(optional($post->user)->name ?? 'X', 0, 1)) }}
                        </div>
                    </div>
                    <div>
                        {{-- PERBAIKAN: Gunakan optional() dan ambil role dari relasi --}}
                        <h6 class="card-title mb-0">{{ optional($post->user)->name ?? 'User Dihapus' }} <small class="text-muted">({{ optional($post->user->roles->first())->name }})</small></h6>
                        <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                        <p class="card-text mt-2">{!! nl2br(e($post->konten)) !!}</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
