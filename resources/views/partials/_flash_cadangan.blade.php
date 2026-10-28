{{--
    Pesan cadangan (sukses/gagal/validasi). Sebagian halaman sudah menampilkan pesannya sendiri; blok ini
    tersembunyi dan hanya dimunculkan bila pesan yang sama tidak tampil di halaman, sehingga tidak ada lagi
    aksi yang gagal "diam-diam" dan tidak ada pesan ganda. Gaya mengikuti alert seragam di seluruh modul.
--}}
@php
    $flashCadangan = collect([
        'success' => ['session' => session('success'), 'kelas' => 'alert-success', 'ikon' => 'bi-check-circle-fill'],
        'error'   => ['session' => session('error'),   'kelas' => 'alert-danger',  'ikon' => 'bi-exclamation-triangle-fill'],
        'warning' => ['session' => session('warning'), 'kelas' => 'alert-warning', 'ikon' => 'bi-exclamation-circle-fill'],
        'info'    => ['session' => session('info'),    'kelas' => 'alert-info',    'ikon' => 'bi-info-circle-fill'],
    ])->filter(fn ($f) => is_string($f['session']) && $f['session'] !== '');
@endphp
@foreach($flashCadangan as $f)
    <div class="alert {{ $f['kelas'] }} alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold d-none" role="alert" data-flash-cadangan data-pesan="{{ $f['session'] }}">
        <i class="bi {{ $f['ikon'] }} me-2"></i> {{ $f['session'] }}
        <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endforeach
@if(isset($errors) && $errors->any())
    <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 small fw-bold d-none" role="alert" data-flash-cadangan data-validasi data-pesan="{{ $errors->first() }}">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <span class="uppercase">Periksa kembali isian formulir:</span>
        <ul class="mb-0 mt-2 ps-3">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if($flashCadangan->isNotEmpty() || (isset($errors) && $errors->any()))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const main = document.querySelector('main');
            document.querySelectorAll('[data-flash-cadangan]').forEach(function (el) {
                const pesan = (el.dataset.pesan || '').trim();
                let sudahTampil = false;
                main.querySelectorAll('.alert, .invalid-feedback, .text-danger, .toast').forEach(function (lain) {
                    if (lain === el || el.contains(lain) || lain.closest('[data-flash-cadangan]')) return;
                    if (pesan && lain.offsetParent !== null && lain.textContent.includes(pesan)) sudahTampil = true;
                });
                if (el.hasAttribute('data-validasi') && main.querySelector('.is-invalid')) sudahTampil = true;
                if (!sudahTampil) el.classList.remove('d-none');
            });
        });
    </script>
@endif
