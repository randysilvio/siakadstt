@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold uppercase mb-0 text-dark">
                <i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Notifikasi
            </h4>
            
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifikasi.baca-semua') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-dark fw-bold uppercase px-3 py-1.5 shadow-sm" style="font-size: 0.75rem;">
                        <i class="bi bi-check2-all me-1"></i> Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>

        <div class="card border-0 shadow-sm border-top border-dark border-4 mb-4 overflow-hidden">
            <div class="list-group list-group-flush">
                @forelse($notifikasinya as $notif)
                    @php
                        \App\Http\Controllers\NotificationController::sanitizeNotificationRecord($notif);
                        $nTitle   = $notif->data['title'] ?? 'Pemberitahuan Sistem';
                        $nMessage = $notif->data['message'] ?? '';
                        $nIcon    = $notif->data['icon'] ?? 'bi-info-circle-fill text-secondary';
                    @endphp
                    <a href="{{ route('notifikasi.baca', $notif->id) }}" 
                       class="list-group-item list-group-item-action p-4 border-bottom {{ $notif->unread() ? 'bg-light-new' : '' }}">
                        
                        <div class="d-flex gap-3 align-items-start">
                            <div class="mt-1 flex-shrink-0">
                                <i class="{{ $nIcon }} fs-3"></i>
                            </div>
                            <div class="w-100" style="min-width: 0; word-break: break-word;">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                                    <h6 class="mb-0 fw-bold uppercase {{ $notif->unread() ? 'text-dark' : 'text-secondary' }}" style="font-size: 0.9rem;">
                                        {{ $nTitle }}
                                    </h6>
                                    <small class="text-muted font-monospace" style="font-size: 0.72rem;">
                                        {{ $notif->created_at->translatedFormat('d M Y, H:i') }}
                                    </small>
                                </div>
                                <p class="mb-2 {{ $notif->unread() ? 'text-dark' : 'text-muted' }}" style="font-size: 0.85rem; line-height: 1.5;">
                                    {{ $nMessage }}
                                </p>
                                
                                @if($notif->unread())
                                    <span class="badge bg-danger px-2 py-1" style="font-size: 0.62rem; letter-spacing: 0.5px;">BELUM DIBACA</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-bell-slash fs-1 d-block mb-3 opacity-50"></i>
                        <h6 class="uppercase fw-bold">Tidak ada riwayat notifikasi</h6>
                        <p class="small mb-0">Pemberitahuan sistem dari SIAKAD akan muncul di sini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="d-flex justify-content-center">
            {{ $notifikasinya->links() }}
        </div>
    </div>
</div>
@endsection