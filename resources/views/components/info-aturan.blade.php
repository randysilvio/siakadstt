@props(['kunci', 'terang' => false])
@php $aturan = \App\Support\InfoAturan::ambil($kunci); @endphp
@if($aturan)
    @php
        $jenis = \App\Support\InfoAturan::JENIS[$aturan['jenis']] ?? \App\Support\InfoAturan::JENIS['kebijakan'];
        $isi = '<ul class="ps-3 mb-2">' . collect($aturan['isi'])->map(fn ($b) => '<li class="mb-1">' . e($b) . '</li>')->implode('') . '</ul>'
             . '<div class="border-top pt-2 text-muted"><strong class="text-dark">Dasar:</strong> ' . e($aturan['dasar']) . '</div>'
             . '<div class="mt-2"><span class="badge rounded-0 bg-' . $jenis['warna'] . ($jenis['warna'] === 'warning' ? ' text-dark' : '') . '">' . e(strtoupper($jenis['label'])) . '</span></div>';
    @endphp
    <a role="button" tabindex="0"
        {{ $attributes->merge(['class' => 'btn btn-link p-0 border-0 align-baseline lh-1 info-aturan ' . ($terang ? 'text-white-50' : 'text-secondary')]) }}
        data-bs-toggle="popover" data-bs-trigger="focus" data-bs-placement="auto" data-bs-html="true"
        data-bs-custom-class="popover-aturan"
        data-bs-title="{{ $aturan['judul'] }}" data-bs-content="{{ $isi }}"
        aria-label="Penjelasan: {{ $aturan['judul'] }}" title="Penjelasan aturan">
        <i class="bi bi-question-circle" style="font-size: .85em;"></i>
    </a>
    @once
        @push('styles')
            <style>
                .info-aturan { text-decoration: none; vertical-align: super; font-size: .9rem; }
                .info-aturan:hover, .info-aturan:focus { color: #0d6efd !important; box-shadow: none; }
                .popover-aturan { --bs-popover-max-width: 340px; --bs-popover-border-radius: 0; --bs-popover-font-size: .78rem; text-transform: none; }
                .popover-aturan .popover-header { font-weight: 700; text-transform: uppercase; font-size: .75rem; letter-spacing: .02em; }
            </style>
        @endpush
    @endonce
@endif
