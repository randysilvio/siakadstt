{{--
    Paginasi standar SIAKAD (Bootstrap 5): berbahasa Indonesia, ringkas di HP (halaman pertama, terakhir,
    dan ±1 di sekitar halaman aktif), serta MEMPERTAHANKAN filter/pencarian di URL saat berpindah halaman.
--}}
@if ($paginator->hasPages())
    @php
        $paginator->appends(collect(request()->query())->except($paginator->getPageName())->all());
        $kini = $paginator->currentPage();
        $akhir = $paginator->lastPage();
        $halaman = collect([1, $akhir, $kini - 1, $kini, $kini + 1])->filter(fn ($p) => $p >= 1 && $p <= $akhir)->unique()->sort()->values();
    @endphp
    <nav class="siakad-paginasi d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2 w-100 mt-2" aria-label="Navigasi halaman">
        <small class="text-muted font-monospace">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ number_format($paginator->total(), 0, ',', '.') }} data
        </small>
        <ul class="pagination pagination-sm mb-0 flex-wrap justify-content-center">
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $paginator->onFirstPage() ? '#' : $paginator->previousPageUrl() }}" aria-label="Sebelumnya" @if($paginator->onFirstPage()) tabindex="-1" aria-disabled="true" @endif>&lsaquo;</a>
            </li>
            @foreach ($halaman as $i => $p)
                @if ($i > 0 && $p - $halaman[$i - 1] > 1)
                    <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                @endif
                <li class="page-item {{ $p === $kini ? 'active' : '' }}" @if($p === $kini) aria-current="page" @endif>
                    @if ($p === $kini)
                        <span class="page-link">{{ $p }}</span>
                    @else
                        <a class="page-link" href="{{ $paginator->url($p) }}">{{ $p }}</a>
                    @endif
                </li>
            @endforeach
            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                <a class="page-link" href="{{ $paginator->hasMorePages() ? $paginator->nextPageUrl() : '#' }}" aria-label="Berikutnya" @unless($paginator->hasMorePages()) tabindex="-1" aria-disabled="true" @endunless>&rsaquo;</a>
            </li>
        </ul>
    </nav>
@endif
