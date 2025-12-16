<div class="d-flex align-items-center mb-4">
    <div class="bg-success bg-opacity-10 text-success rounded p-2 me-3">
        <i class="bi bi-chat-quote-fill fs-4"></i>
    </div>
    <div>
        <h5 class="mb-0 fw-bold text-dark">Forum Diskusi</h5>
        <small class="text-muted">Ruang tanya jawab dan diskusi kelas.</small>
    </div>
</div>

{{-- Form Input --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body bg-light rounded">
        <form action="{{ route('verum.forum.store', $verum_kela) }}" method="POST">
            @csrf
            <div class="d-flex align-items-start">
                <div class="me-3 d-none d-md-block">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px;">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                </div>
                <div class="flex-grow-1">
                    <textarea class="form-control border-0 shadow-none mb-2" name="konten" rows="2" placeholder="Apa yang ingin Anda diskusikan?" style="background: transparent; resize: none;"></textarea>
                    <div class="d-flex justify-content-end border-top pt-2">
                        <button type="submit" class="btn btn-primary btn-sm px-4 rounded-pill">
                            <i class="bi bi-send-fill me-1"></i> Kirim
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- List Postingan --}}
@if($verum_kela->postingan->isEmpty())
    <div class="text-center py-5">
        <img src="https://cdn-icons-png.flaticon.com/512/134/134808.png" width="64" class="opacity-25 mb-3">
        <p class="text-muted">Belum ada diskusi. Jadilah yang pertama memulai!</p>
    </div>
@else
    <div class="vstack gap-3">
        @foreach($verum_kela->postingan as $post)
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex">
                        <div class="me-3">
                            <div class="bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 45px; height: 45px; background-color: #4e73df;">
                                {{ strtoupper(substr(optional($post->user)->name ?? 'X', 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 fw-bold text-dark">
                                    {{ optional($post->user)->name ?? 'User Dihapus' }}
                                    @if(optional($post->user)->hasRole('dosen'))
                                        <i class="bi bi-patch-check-fill text-primary ms-1" title="Dosen"></i>
                                    @endif
                                </h6>
                                <small class="text-muted" style="font-size: 0.75rem;">{{ $post->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="text-secondary mb-0 small" style="line-height: 1.5;">
                                {!! nl2br(e($post->konten)) !!}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif