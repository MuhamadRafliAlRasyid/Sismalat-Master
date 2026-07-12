@extends('layouts.app')

@section('title', 'Pengembalian Alat')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header --}}
        <div
            class="relative overflow-hidden bg-gradient-to-br from-amber-50 via-white to-orange-50 rounded-3xl p-6 sm:p-8 mb-8 border border-amber-100/50 shadow-sm">
            <div
                class="absolute top-0 right-0 w-64 h-64 bg-amber-200/20 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl">
            </div>
            <div
                class="absolute bottom-0 left-0 w-48 h-48 bg-orange-200/20 rounded-full translate-y-1/2 -translate-x-1/2 blur-3xl">
            </div>

            <div class="relative flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span
                            class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-lg shadow-amber-200">
                            <i class="fas fa-undo-alt"></i>
                        </span>
                        <h2
                            class="text-3xl font-bold bg-gradient-to-r from-amber-700 to-orange-700 bg-clip-text text-transparent">
                            Pengembalian Alat
                        </h2>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 ml-13">Riwayat pengembalian alat yang telah dipinjam</p>
                </div>
                <div class="flex gap-3 flex-wrap">
                    <a href="{{ route('pengambilan_alat.index') }}"
                        class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 hover:bg-amber-50 text-amber-700 border border-amber-200 hover:border-amber-300 px-4 py-2.5 rounded-xl font-medium transition-all duration-300 shadow-sm">
                        <i class="fas fa-list"></i> Data Peminjaman
                    </a>
                    @if (in_array(auth()->user()->role, ['admin', 'super']))
                        <a href="{{ route('pengembalian_alat.export') }}"
                            class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 hover:bg-red-50 text-red-600 border border-red-200 hover:border-red-300 px-4 py-2.5 rounded-xl font-medium transition-all duration-300 shadow-sm hover:shadow-md">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Alert Success --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" x-init="setTimeout(() => show = false, 4000)"
                class="mb-6 bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
                <span class="flex-shrink-0 w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <i class="fas fa-check-circle text-emerald-500"></i>
                </span>
                <span class="flex-1 font-medium">{{ session('success') }}</span>
                <button @click="show = false"
                    class="flex-shrink-0 w-8 h-8 rounded-xl hover:bg-emerald-100 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-emerald-400"></i>
                </button>
            </div>
        @endif

        {{-- Filter --}}
        <form method="GET" class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                <div class="md:col-span-6 relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama alat atau user..."
                        class="w-full pl-11 pr-4 py-3.5 bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-2xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all duration-300 shadow-sm">
                </div>
                <div class="md:col-span-3">
                    <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                        class="w-full px-4 py-3.5 bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-2xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all duration-300 shadow-sm">
                </div>
                <div class="md:col-span-3 flex gap-2">
                    <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-medium px-6 py-3.5 rounded-2xl transition-all duration-300 shadow-lg shadow-amber-200 hover:shadow-xl hover:-translate-y-0.5">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    @if (request('search') || request('tanggal'))
                        <a href="{{ route('pengembalian_alat.index') }}"
                            class="inline-flex items-center justify-center px-4 py-3.5 border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 hover:bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 text-gray-500 dark:text-gray-400 rounded-2xl transition-all">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- List --}}
        <div class="space-y-4">
            @forelse($data as $i => $d)
                @php
                    $isAdmin = in_array(auth()->user()->role, ['admin', 'super']);
                    $statusAlat = $d->pengambilan->alat->status ?? 'ok';
                @endphp
                <div
                    class="group relative bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-amber-200 overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-amber-400 to-orange-500"></div>

                    <div class="p-5 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                            {{-- Thumbnail --}}
                            <div class="flex-shrink-0 flex items-center gap-3">
                                <div class="relative">
                                    <div
                                        class="w-16 h-16 rounded-2xl bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 flex items-center justify-center overflow-hidden border border-gray-100 dark:border-gray-700 group-hover:border-amber-200 transition-colors">
                                        @if ($d->pengambilan && $d->pengambilan->alat && $d->pengambilan->alat->foto)
                                            <img src="{{ $d->pengambilan->alat->foto_thumb }}"
                                                alt="{{ $d->pengambilan->alat->nama_alat }}" loading="lazy"
                                                class="w-full h-full object-cover"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="w-full h-full items-center justify-center text-gray-400 hidden">
                                                <i class="fas fa-tools text-2xl"></i>
                                            </div>
                                        @else
                                            <i class="fas fa-tools text-2xl text-gray-300"></i>
                                        @endif
                                    </div>
                                    <div
                                        class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center border-2 border-white shadow-sm">
                                        <i class="fas fa-check-circle text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- Info Utama --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <div>
                                        <h3
                                            class="font-bold text-gray-800 text-lg truncate group-hover:text-amber-700 transition-colors">
                                            {{ $d->pengambilan->alat->nama_alat ?? 'Alat Tidak Diketahui' }}
                                        </h3>
                                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 ring-1 ring-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full mr-1.5 bg-amber-500"></span>
                                                Dikembalikan
                                            </span>
                                            {{-- Status Kalibrasi Alat --}}
                                            @if ($statusAlat == 'expired')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 ring-1 ring-red-200">
                                                    <i class="fas fa-exclamation-circle mr-1"></i> Expired
                                                </span>
                                            @elseif ($statusAlat == 'warning')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i> Warning
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <span
                                            class="flex-shrink-0 w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center">
                                            <i class="fas fa-user text-amber-500 text-xs"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-xs text-gray-400">Pengguna</p>
                                            <p class="font-medium text-gray-700 truncate">{{ $d->user->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <span
                                            class="flex-shrink-0 w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center">
                                            <i class="fas fa-boxes text-amber-500 text-xs"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-xs text-gray-400">Jumlah</p>
                                            <p class="font-medium text-amber-600">{{ $d->jumlah }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <span
                                            class="flex-shrink-0 w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center">
                                            <i class="fas fa-calendar-check text-amber-500 text-xs"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-xs text-gray-400">Tanggal</p>
                                            <p class="font-medium text-gray-700">
                                                {{ \Carbon\Carbon::parse($d->tanggal_pengembalian)->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                </div>

                                @if ($d->keterangan)
                                    <div class="mt-2 flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-sticky-note text-amber-400 w-4"></i>
                                        <span class="truncate max-w-xs">{{ Str::limit($d->keterangan, 60) }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex sm:flex-col gap-2 justify-end">
                                <a href="{{ route('pengembalian_alat.show', $d->hashid) }}"
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700 transition-all duration-200 hover:scale-105"
                                    title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>

                                {{-- Tombol Kalibrasi jika alat expired/warning --}}
                                @if (in_array($statusAlat, ['expired', 'warning']) && $d->pengambilan && $d->pengambilan->alat)
                                    <a href="{{ route('kalibrasi.create', $d->pengambilan->alat->hashid) }}"
                                        class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-yellow-50 text-yellow-600 hover:bg-yellow-100 hover:text-yellow-700 transition-all duration-200 hover:scale-105"
                                        title="Kalibrasi Alat">
                                        <i class="fas fa-tools"></i>
                                    </a>
                                @endif

                                {{-- Edit & Hapus hanya untuk admin/super --}}
                                @if ($isAdmin)
                                    <a href="{{ route('pengembalian_alat.edit', $d->hashid) }}"
                                        class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 transition-all duration-200 hover:scale-105"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('pengembalian_alat.destroy', $d->hashid) }}"
                                        onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 transition-all duration-200 hover:scale-105"
                                            title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-20">
                    <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-amber-50 mb-6">
                        <i class="fas fa-undo-alt text-4xl text-amber-300"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Data Pengembalian</h3>
                    <p class="text-gray-400 mb-6">Data pengembalian akan muncul setelah alat dikembalikan</p>
                    <a href="{{ route('pengambilan_alat.index') }}"
                        class="inline-flex items-center gap-2 bg-amber-100 hover:bg-amber-200 text-amber-700 px-6 py-3 rounded-2xl font-medium transition-all">
                        <i class="fas fa-list"></i> Lihat Data Peminjaman
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($data->hasPages())
            <div class="mt-8 flex justify-center">
                <div
                    class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 px-4 py-3">
                    {{ $data->onEachSide(1)->links('vendor.pagination.tailwind') }}
                </div>
            </div>
        @endif
    </div>
@endsection
