@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Kalender Akademik</h3>
            <span class="text-muted small uppercase">Agenda Operasional STT GPI PAPUA</span>
        </div>
        @auth @if(Auth::user()->roles->contains('name', 'admin'))
            <a href="{{ route('admin.kalender.index') }}" class="btn btn-sm btn-dark rounded-0 px-4 fw-bold uppercase small">PUSAT MANAJEMEN</a>
        @endif @endauth
    </div>
    
    <div class="card border-0 shadow-sm rounded-0 border-top border-primary border-4">
        <div class="card-body p-4 bg-white">
            <div id='calendar'></div>
        </div>
    </div>

    {{-- LEGENDA FORMAL --}}
    <div class="p-3 bg-light border mt-4 d-flex gap-5 justify-content-center">
        <div class="d-flex align-items-center small uppercase fw-bold"><span class="d-inline-block rounded-0 me-2" style="width: 12px; height: 12px; background: #dc3545;"></span> AGENDA DOSEN</div>
        <div class="d-flex align-items-center small uppercase fw-bold"><span class="d-inline-block rounded-0 me-2" style="width: 12px; height: 12px; background: #ffc107;"></span> AGENDA MAHASISWA</div>
        <div class="d-flex align-items-center small uppercase fw-bold"><span class="d-inline-block rounded-0 me-2" style="width: 12px; height: 12px; background: #0d6efd;"></span> AGENDA UMUM</div>
    </div>
</div>

{{-- MODAL DETAIL --}}
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-0 border-0 shadow">
      <div class="modal-header bg-dark text-white py-3">
        <h6 class="modal-title fw-bold uppercase mb-0 small">Rincian Kegiatan Akademik</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <h4 id="eventTitle" class="fw-bold text-dark uppercase mb-3 border-bottom pb-2"></h4>
        <div class="mb-4">
            <small class="text-muted uppercase fw-bold d-block mb-1" style="font-size: 0.65rem;">Waktu Pelaksanaan:</small>
            <div id="eventDate" class="fw-bold font-monospace text-primary"></div>
        </div>
        <div class="bg-light p-3 border rounded-0">
            <small class="text-muted uppercase fw-bold d-block mb-2" style="font-size: 0.65rem;">Deskripsi / Keterangan:</small>
            <div id="eventDescription" class="small text-secondary" style="white-space: pre-line;"></div>
        </div>
      </div>
      <div class="modal-footer bg-light border-0 py-2">
        <button type="button" class="btn btn-sm btn-dark rounded-0 uppercase px-4 fw-bold small" data-bs-dismiss="modal">TUTUP</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.11/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                initialView: 'dayGridMonth',
                headerToolbar: {left:'prev,next today', center:'title', right:'dayGridMonth,listWeek'},
                events: '{{ route("kalender.events") }}',
                locale: 'id',
                buttonText: {today:'Hari Ini', month:'Bulanan', list:'Daftar Agenda'},
                height: 'auto',
                aspectRatio: 1.8,
                eventDataTransform: function(eventInfo) {
                    if(eventInfo.target_role === 'dosen') {
                        eventInfo.backgroundColor = '#dc3545'; eventInfo.borderColor = '#dc3545';
                    } else if(eventInfo.target_role === 'mahasiswa') {
                        eventInfo.backgroundColor = '#ffc107'; eventInfo.borderColor = '#ffc107'; eventInfo.textColor = '#000';
                    } else {
                        eventInfo.backgroundColor = '#0d6efd'; eventInfo.borderColor = '#0d6efd';
                    }
                    return eventInfo;
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    document.getElementById('eventTitle').innerText = info.event.title;
                    let start = info.event.start.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                    document.getElementById('eventDate').innerText = start;
                    document.getElementById('eventDescription').innerText = info.event.extendedProps.deskripsi || 'Informasi deskripsi tidak tersedia.';
                    new bootstrap.Modal(document.getElementById('eventDetailModal')).show();
                }
            });
            calendar.render();
        });
    </script>
@endpush