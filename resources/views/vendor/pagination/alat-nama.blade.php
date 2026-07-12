@if ($paginator->hasPages())
    @php
        $pageNames = $paginator->pageNames ?? [];
    @endphp

    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between flex-wrap gap-3">
        {{-- Info total --}}
        <div class="w-full sm:w-auto text-center sm:text-left">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Halaman <span class="font-semibold text-amber-600">{{ $paginator->currentPage() }}</span>
                dari <span class="font-semibold">{{ $paginator->lastPage() }}</span>
                &middot; Total <span class="font-semibold">{{ $paginator->total() }}</span> jenis alat
            </p>
        </div>

        {{-- Tombol Navigasi --}}
        <div class="flex items-center gap-2 flex-wrap justify-center">
            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span
                    class="inline-flex items-center gap-1 px-3 py-2 text-xs font-medium text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-lg cursor-not-allowed">
                    <i class="fas fa-chevron-left text-[10px]"></i> Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                    class="inline-flex items-center gap-1 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-amber-50 hover:border-amber-300 hover:text-amber-700 transition">
                    <i class="fas fa-chevron-left text-[10px]"></i> Sebelumnya
                </a>
            @endif

            {{-- Tombol Halaman dengan Nama Alat --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span
                        class="inline-flex items-center px-3 py-2 text-xs font-medium text-gray-500 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        {{ $element }}
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @php
                            $alatName = $pageNames[$page] ?? 'Halaman ' . $page;
                            $displayName = strlen($alatName) > 18 ? substr($alatName, 0, 15) . '...' : $alatName;
                        @endphp

                        @if ($page == $paginator->currentPage())
                            {{-- Halaman Aktif --}}
                            <span title="{{ $alatName }}"
                                class="inline-flex items-center gap-1 px-3 py-2 text-xs font-bold text-white bg-amber-500 rounded-lg shadow-md shadow-amber-200 cursor-default">
                                <i class="fas fa-folder-open text-[10px]"></i> {{ $displayName }}
                            </span>
                        @else
                            {{-- Halaman Lain --}}
                            <a href="{{ $url }}" title="{{ $alatName }}"
                                class="inline-flex items-center gap-1 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-amber-50 hover:border-amber-300 hover:text-amber-700 transition">
                                <i class="fas fa-folder text-[10px] text-amber-400"></i> {{ $displayName }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol Selanjutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                    class="inline-flex items-center gap-1 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-amber-50 hover:border-amber-300 hover:text-amber-700 transition">
                    Selanjutnya <i class="fas fa-chevron-right text-[10px]"></i>
                </a>
            @else
                <span
                    class="inline-flex items-center gap-1 px-3 py-2 text-xs font-medium text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-lg cursor-not-allowed">
                    Selanjutnya <i class="fas fa-chevron-right text-[10px]"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
