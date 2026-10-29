{{-- Paginasi sederhana (tanpa total) — gaya SIAKAD, mempertahankan filter di URL. --}}
@if ($paginator->hasPages())
    @php $paginator->appends(collect(request()->query())->except($paginator->getPageName())->all()); @endphp
    <nav class="siakad-paginasi d-flex justify-content-center mt-2" aria-label="Navigasi halaman">
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}"><a class="page-link" href="{{ $paginator->onFirstPage() ? '#' : $paginator->previousPageUrl() }}">&lsaquo; Sebelumnya</a></li>
            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}"><a class="page-link" href="{{ $paginator->hasMorePages() ? $paginator->nextPageUrl() : '#' }}">Berikutnya &rsaquo;</a></li>
        </ul>
    </nav>
@endif
