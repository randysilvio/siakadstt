@extends('layouts.app')

@push('styles')
    <style>
        /* Modern FullCalendar Styling */
        .fc-event { border: none !important; border-radius: 4px; padding: 4px 8px; font-size: 0.9em; cursor: pointer; transition: transform 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .fc-event:hover { transform: scale(1.02); z-index: 10; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .fc-day-today { background-color: #f8fbff !important; }
        .fc-toolbar-title { font-weight: 700; font-size: 1.5rem !important; color: #333; }
        .fc-button-primary { background-color: #0d6efd !important; border-color: #0d6efd !important; text-transform: capitalize; }
        .fc-button-primary:hover { background-color: #0b5ed7 !important; }
        .fc-button-active { background-color: #0a58ca !important; }
    </style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-0"><i class="bi bi-calendar3 me-2"></i>Kalender Akademik</h2>
            <p class="text-muted mb-0">Agenda kegiatan akademik semester ini.</p>
        </div>
        
        @auth
            @if(Auth::user()->roles->contains('name', 'admin'))
                <a href="{{ route('admin.kalender.index') }}" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="bi bi-gear me-1"></i> Kelola Kegiatan
                </a>
            @endif
        @endauth
    </div>
    
    <div class="card border-0 shadow-lg rounded-3 overflow-hidden">
        <div class="card-body p-4">
            <div id='calendar'></div>
        </div>
    </div>
</div>

{{-- Modern Event Modal --}}
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="eventDetailModalLabel">Detail Kegiatan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <h4 id="eventTitle" class="mb-3 text-dark fw-bold"></h4>
        
        <div class="d-flex align-items-start mb-3">
            <div class="me-3 text-primary"><i class="bi bi-clock fs-4"></i></div>
            <div>
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Waktu Pelaksanaan</small>
                <div id="eventDate" class="fw-semibold"></div>
            </div>
        </div>

        <div class="d-flex align-items-start">
            <div class="me-3 text-primary"><i class="bi bi-justify-left fs-4"></i></div>
            <div>
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Deskripsi</small>
                <div id="eventDescription" class="text-secondary" style="white-space: pre-line;"></div>
            </div>
        </div>
      </div>
      <div class="modal-footer bg-light border-0">
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.11/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var eventDetailModal = new bootstrap.Modal(document.getElementById('eventDetailModal'));

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {left:'prev,next today', center:'title', right:'dayGridMonth,listWeek'},
                events: '{{ route("kalender.events") }}',
                locale: 'id',
                buttonText: {today:'Hari Ini', month:'Bulan', list:'Agenda'},
                eventColor: '#3788d8',
                height: 'auto',
                aspectRatio: 1.8,
                
                eventDataTransform: function(eventInfo) {
                    // Warna dinamis berdasarkan target
                    if(eventInfo.target_role === 'dosen') {
                        eventInfo.backgroundColor = '#dc3545'; // Merah
                        eventInfo.borderColor = '#dc3545';
                    } else if(eventInfo.target_role === 'mahasiswa') {
                        eventInfo.backgroundColor = '#ffc107'; // Kuning
                        eventInfo.borderColor = '#ffc107';
                        eventInfo.textColor = '#000';
                    } else {
                        eventInfo.backgroundColor = '#0d6efd'; // Biru (Umum)
                        eventInfo.borderColor = '#0d6efd';
                    }
                    return eventInfo;
                },

                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    
                    // Isi Modal
                    document.getElementById('eventTitle').innerText = info.event.title;
                    
                    // Format Tanggal
                    let startDate = info.event.start.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                    let endDateStr = startDate;
                    
                    if (info.event.end) {
                        // FullCalendar end date is exclusive, so we subtract 1ms
                        let endDate = new Date(info.event.end.valueOf() - 1);
                        // Cek jika beda hari
                        if (info.event.start.toDateString() !== endDate.toDateString()) {
                            let endStr = endDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                            endDateStr = `${startDate} — ${endStr}`;
                        }
                    }
                    
                    document.getElementById('eventDate').innerText = endDateStr;
                    document.getElementById('eventDescription').innerText = info.event.extendedProps.deskripsi || 'Tidak ada deskripsi tambahan.';
                    
                    eventDetailModal.show();
                }
            });
            calendar.render();
        });
    </script>
@endpush