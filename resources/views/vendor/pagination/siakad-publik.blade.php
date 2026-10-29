{{-- Paginasi halaman publik (Tailwind): berbahasa Indonesia, ringkas, mempertahankan filter di URL. --}}
@if ($paginator->hasPages())
    @php
        $paginator->appends(collect(request()->query())->except($paginator->getPageName())->all());
        $kini = $paginator->currentPage();
        $akhir = $paginator->lastPage();
        $halaman = collect([1, $akhir, $kini - 1, $kini, $kini + 1])->filter(fn ($p) => $p >= 1 && $p <= $akhir)->unique()->sort()->values();
        $dasar = 'min-w-[2.25rem] h-9 px-3 inline-flex items-center justify-center text-sm font-semibold border rounded-md transition';
    @endphp
    <nav class="flex flex-col sm:flex-row items-center justify-between gap-3 w-full" aria-label="Navigasi halaman">
        <p class="text-sm text-gray-500">Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ number_format($paginator->total(), 0, ',', '.') }}</p>
        <div class="flex flex-wrap items-center justify-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="{{ $dasar }} text-gray-300 border-gray-200 cursor-not-allowed">&lsaquo;</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="{{ $dasar }} text-gray-700 border-gray-300 bg-white hover:bg-gray-50" aria-label="Sebelumnya">&lsaquo;</a>
            @endif
            @foreach ($halaman as $i => $p)
                @if ($i > 0 && $p - $halaman[$i - 1] > 1)
                    <span class="{{ $dasar }} text-gray-400 border-transparent">&hellip;</span>
                @endif
                @if ($p === $kini)
                    <span class="{{ $dasar }} text-white bg-slate-800 border-slate-800" aria-current="page">{{ $p }}</span>
                @else
                    <a href="{{ $paginator->url($p) }}" class="{{ $dasar }} text-gray-700 border-gray-300 bg-white hover:bg-gray-50">{{ $p }}</a>
                @endif
            @endforeach
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="{{ $dasar }} text-gray-700 border-gray-300 bg-white hover:bg-gray-50" aria-label="Berikutnya">&rsaquo;</a>
            @else
                <span class="{{ $dasar }} text-gray-300 border-gray-200 cursor-not-allowed">&rsaquo;</span>
            @endif
        </div>
    </nav>
@endif
