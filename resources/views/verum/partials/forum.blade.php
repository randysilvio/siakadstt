{{-- HEADER FORUM --}}
<div class="d-flex align-items-center mb-4 border-bottom border-dark border-2 pb-3">
    <div class="bg-dark text-white rounded-0 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
        <i class="bi bi-chat-square-text-fill fs-5"></i>
    </div>
    <div>
        <h5 class="mb-0 fw-bold text-dark uppercase">Forum Diskusi Ilmiah</h5>
        <span class="text-muted small uppercase font-monospace">Ruang Komunikasi & Pembahasan Studi Kasus Terpusat</span>
    </div>
</div>

{{-- FORM INPUT DISKUSI --}}
<div class="card border-0 shadow-sm mb-5 rounded-0 border-top border-dark border-4 bg-light">
    <div class="card-body p-4">
        <form action="{{ route('verum.forum.store', $verum_kela) }}" method="POST">
            @csrf
            <div class="d-flex align-items-start">
                <div class="me-3 d-none d-md-block">
                    {{-- Avatar Kotak Siku Presisi 0px --}}
                    <div class="bg-dark text-white rounded-0 d-flex align-items-center justify-content-center fw-bold font-monospace" style="width: 45px; height: 45px; font-size: 16px;">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                </div>
                <div class="flex-grow-1">
                    <textarea class="form-control rounded-0 border border-dark border-opacity-25 mb-3 uppercase" name="konten" rows="3" placeholder="TULISKAN TOPIK ATAU PERTANYAAN DISKUSI..." style="resize: none;" required></textarea>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm py-2">
                            <i class="bi bi-send-fill me-2 text-white align-middle"></i> Kirim Diskusi
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- DAFTAR POSTINGAN --}}
@if($verum_kela->postingan->isEmpty())
    <div class="text-center py-5 bg-white border border-dark border-opacity-25 rounded-0">
        <i class="bi bi-chat-square-dots fs-2 d-block mb-2 text-dark opacity-25"></i>
        <p class="text-muted small uppercase fw-bold mb-0">Belum ada topik pembahasan yang dimulai pada kelas ini.</p>
    </div>
@else
    <div class="d-grid gap-3 mb-5">
        @foreach($verum_kela->postingan as $post)
            <div class="card border-0 shadow-sm rounded-0 border-start border-dark border-4 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            {{-- Avatar Pengirim Siku Tajam --}}
                            <div class="bg-secondary text-white rounded-0 d-flex align-items-center justify-content-center fw-bold font-monospace" style="width: 45px; height: 45px; font-size: 16px;">
                                {{ strtoupper(substr(optional($post->user)->name ?? 'X', 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                <div class="d-flex align-items-center">
                                    <h6 class="mb-0 fw-bold text-dark uppercase fs-6">
                                        {{ optional($post->user)->name ?? 'USER DIHAPUS' }}
                                    </h6>
                                    @if(optional($post->user)->hasRole('dosen'))
                                        <span class="badge bg-dark text-white rounded-0 font-monospace uppercase fw-bold ms-2 px-2 py-1" style="font-size: 9px;">DOSEN PENGAMPU</span>
                                    @endif
                                </div>
                                <span class="text-muted font-monospace uppercase" style="font-size: 11px;">
                                    {{ strtoupper($post->created_at->diffForHumans()) }}
                                </span>
                            </div>
                            {{-- Konten Diskusi --}}
                            <p class="text-dark small uppercase mb-0" style="line-height: 1.75; text-align: justify;">
                                {!! nl2br(e($post->konten)) !!}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif