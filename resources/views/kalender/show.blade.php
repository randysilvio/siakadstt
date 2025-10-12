@extends('layouts.app')

@push('styles')
    <style>
        /* CSS hanya untuk merapikan event, semua style spinner dihapus */
        .fc-event{border:1px solid rgba(255,255,255,0.8)!important;padding:3px 5px!important;cursor:pointer;font-weight:500}
        .fc-event-main-frame{overflow:hidden;text-overflow:ellipsis}
        .fc-day-today{background-color:#f0f8ff!important}
    </style>
@endpush

@section('content')
<div class="container">
    {{-- ================================================================= --}}
    {{-- KODE YANG DIPERBAIKI ADA DI BLOK INI --}}
    {{-- ================================================================= --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Kalender Akademik</h2>
        {{-- PERBAIKAN: Cek relasi 'roles' bukan properti 'role' dan sesuaikan route --}}
        @auth
            @if(Auth::user()->roles->contains('name', 'admin'))
                <a href="{{ route('admin.kalender.index') }}" class="btn btn-outline-primary">Manajemen Kalender</a>
            @endif
        @endauth
    </div>
    {{-- ================================================================= --}}
    {{-- AKHIR DARI BLOK PERBAIKAN --}}
    {{-- ================================================================= --}}
    
    <div class="card">
        <div class="card-body">
            <div id='calendar'></div>
        </div>
    </div>
</div>

<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-labelledby="eventDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="eventDetailModalLabel">Detail Kegiatan</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body"><p><strong>Tanggal:</strong><span id="eventDate"></span></p><p><strong>Deskripsi:</strong></p><p id="eventDescription"></p></div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div>
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
                headerToolbar: {left:'prev,next today',center:'title',right:'dayGridMonth,timeGridWeek,listWeek'},
                
                events: '{{ route("kalender.events") }}',

                locale: 'id',
                buttonText: {today:'Hari Ini',month:'Bulan',week:'Minggu',list:'Agenda'},
                eventOverlap: false,
                slotEventOverlap: false,
                eventDataTransform: function(eventInfo) {
                    switch (eventInfo.target_role) {
                        case 'dosen':
                            eventInfo.backgroundColor = '#dc3545';
                            eventInfo.borderColor = '#dc3545';
                            break;
                        case 'mahasiswa':
                            eventInfo.backgroundColor = '#ffc107';
                            eventInfo.borderColor = '#ffc107';
                            eventInfo.textColor = '#000';
                            break;
                        default:
                            eventInfo.backgroundColor = '#0d6efd';
                            eventInfo.borderColor = '#0d6efd';
                            break;
                    }
                    return eventInfo;
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    document.getElementById('eventDetailModalLabel').innerText=info.event.title;
                    let startDate=info.event.start.toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'});
                    let endDate=info.event.end?new Date(info.event.end.valueOf()-1):info.event.start;
                    let endDateStr=endDate.toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'});
                    if(startDate===endDateStr){document.getElementById('eventDate').innerText=startDate;}else{document.getElementById('eventDate').innerText=`${startDate} - ${endDateStr}`;}
                    document.getElementById('eventDescription').innerText=info.event.extendedProps.deskripsi||'Tidak ada deskripsi.';
                    eventDetailModal.show();
                }
            });
            calendar.render();
        });
    </script>
@endpush