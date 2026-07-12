@extends('layouts.app')

@section('title', 'Data Alat')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ init: false }" x-init="init = true">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                <i class="fas fa-tools text-amber-500"></i> Data Alat
            </h2>
            <div class="flex gap-3">
                <a href="{{ route('alats.create') }}"
                    class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl font-medium flex items-center gap-2 transition shadow-md shadow-amber-200 hover:shadow-lg hover:shadow-amber-300 transform hover:-translate-y-0.5">
                    <i class="fas fa-plus"></i> Tambah Alat
                </a>
                <a href="{{ route('alats.trashed') }}"
                    class="bg-red-500 hover:bg-red-600 text-white px-5 py-2.5 rounded-xl font-medium flex items-center gap-2 transition shadow-md shadow-red-200 hover:shadow-lg hover:shadow-red-300 transform hover:-translate-y-0.5">
                    <i class="fas fa-trash-restore"></i> Data Terhapus
                </a>
            </div>
        </div>

        {{-- Search & Filter --}}
        <form method="GET" class="mb-8 flex flex-col md:flex-row gap-3">
            <div class="flex-1 relative">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama alat, merk, tipe..."
                    class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 pl-11 bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition">
                <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
            </div>
            <select name="kategori_id"
                class="border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition">
                <option value="">-- Semua Kategori --</option>
                @foreach ($kategoris ?? [] as $k)
                    <option value="{{ $k->id }}" {{ request('kategori_id') == $k->id ? 'selected' : '' }}>
                        {{ $k->nama }}
                    </option>
                @endforeach
            </select>
            <button type="submit"
                class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-3 rounded-xl font-medium transition flex items-center gap-2 shadow-sm">
                <i class="fas fa-filter"></i> Filter
            </button>
            @if (request('search') || request('kategori_id'))
                <a href="{{ route('alats.index') }}"
                    class="px-4 py-3 border border-gray-200 dark:border-gray-700 text-gray-600 hover:text-gray-800 rounded-xl flex items-center gap-2 transition bg-white dark:bg-gray-800">
                    <i class="fas fa-times"></i> Reset
                </a>
            @endif
        </form>

        {{-- Grid Folder --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @forelse ($alats as $namaAlatNormalized => $alatItems)
                @php
                    $isAdmin = in_array(auth()->user()->role, ['admin', 'super']);

                    // Ambil nama asli yang paling umum (yang paling sering muncul)
                    $namaAlatDisplay = $alatItems->pluck('nama_alat')->countBy()->sortDesc()->keys()->first();

                    // Tentukan status folder berdasarkan status terburuk
                    $hasExpired = $alatItems->contains(fn($a) => $a->status === 'expired');
                    $hasWarning = $alatItems->contains(fn($a) => $a->status === 'warning');
                    $statusAlat = $hasExpired ? 'expired' : ($hasWarning ? 'warning' : 'active');
                @endphp

                <div x-data="{ open: false }" x-intersect="$el.classList.add('animate-fadeIn')"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-amber-100 hover:shadow-xl hover:border-amber-300 transition-all duration-300 overflow-hidden">

                    {{-- Folder Header --}}
                    <div class="p-5 flex justify-between items-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                        @click="open = !open">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-2xl shadow-sm">
                                <i class="fas" :class="open ? 'fa-folder-open' : 'fa-folder'"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 text-xl leading-tight">{{ $namaAlatDisplay }}</h3>
                                <p class="text-sm text-gray-500">{{ $alatItems->count() }} varian (Kapasitas/Kelas berbeda)
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span
                                class="text-xs px-3 py-1.5 rounded-full font-semibold whitespace-nowrap
                                @if ($statusAlat == 'expired') bg-red-100 text-red-700 animate-pulse
                                @elseif($statusAlat == 'warning') bg-yellow-100 text-yellow-700
                                @else bg-emerald-100 text-emerald-700 @endif">
                                {{ ucfirst($statusAlat) }}
                            </span>
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-300"
                                :class="open ? 'rotate-180' : ''"></i>
                        </div>
                    </div>

                    {{-- Folder Content (Items) --}}
                    <div x-show="open" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-2">

                        <div
                            class="px-5 pb-5 pt-2 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                @foreach ($alatItems as $alat)
                                    @php $itemStatus = $alat->status; @endphp
                                    <div
                                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex flex-col">
                                        <div class="flex gap-3 mb-3">
                                            {{-- Thumbnail --}}
                                            <div class="w-16 h-16 rounded-lg bg-gray-100 dark:bg-gray-700 flex-shrink-0 overflow-hidden cursor-pointer"
                                                @click="$dispatch('quick-view', { url: '{{ $alat->foto_url }}', name: '{{ $alat->nama_alat }}' })">
                                                @if ($alat->foto)
                                                    <img src="{{ $alat->foto_thumb }}" alt="{{ $alat->nama_alat }}"
                                                        loading="lazy" class="w-full h-full object-cover"
                                                        onerror="this.onerror=null; this.parentNode.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-gray-300\'><i class=\'fas fa-tools text-2xl\'></i></div>';">
                                                @else
                                                    <div
                                                        class="w-full h-full flex items-center justify-center text-gray-300">
                                                        <i class="fas fa-tools text-2xl"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex justify-between items-start gap-2 mb-1">
                                                    <span
                                                        class="text-xs px-2 py-0.5 rounded-full font-semibold whitespace-nowrap
                                                        @if ($itemStatus == 'expired') bg-red-100 text-red-700
                                                        @elseif($itemStatus == 'warning') bg-yellow-100 text-yellow-700
                                                        @else bg-emerald-100 text-emerald-700 @endif">
                                                        {{ ucfirst($itemStatus) }}
                                                    </span>
                                                </div>
                                                {{-- Info Status Hari --}}
                                                @if ($alat->status_hari !== null)
                                                    @if ($itemStatus == 'warning')
                                                        <p class="text-[10px] text-yellow-600 mb-1">⚠️
                                                            {{ $alat->status_hari }} hari lagi</p>
                                                    @elseif ($itemStatus == 'expired')
                                                        <p class="text-[10px] text-red-600 mb-1">❌ Expired
                                                            {{ abs($alat->status_hari) }} hari</p>
                                                    @endif
                                                @endif
                                                <div class="space-y-1 text-xs text-gray-600">
                                                    <div class="truncate"><span class="text-gray-400">Merk:</span> <span
                                                            class="font-medium">{{ $alat->merk }}</span></div>
                                                    <div class="truncate"><span class="text-gray-400">Kapasitas:</span>
                                                        <span
                                                            class="font-bold text-amber-600">{{ $alat->kapasitas ?: '-' }}</span>
                                                    </div>
                                                    <div class="truncate"><span class="text-gray-400">Kelas:</span> <span
                                                            class="font-medium">{{ $alat->kelas ?: '-' }}</span></div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Actions --}}
                                        <div
                                            class="flex gap-2 justify-end mt-auto pt-3 border-t border-gray-100 dark:border-gray-700">
                                            <a href="{{ route('alats.show', $alat->hashid) }}"
                                                class="p-1.5 bg-amber-100 text-amber-700 rounded-lg hover:bg-amber-200 transition"
                                                title="Detail"><i class="fas fa-eye text-xs"></i></a>
                                            @if (in_array($itemStatus, ['expired', 'warning']))
                                                <a href="{{ route('kalibrasis.create', $alat->hashid) }}"
                                                    class="p-1.5 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition"
                                                    title="Kalibrasi"><i class="fas fa-tools text-xs"></i></a>
                                            @endif
                                            @if ($isAdmin)
                                                <a href="{{ route('alats.edit', $alat->hashid) }}"
                                                    class="p-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition"
                                                    title="Edit"><i class="fas fa-edit text-xs"></i></a>
                                                <button
                                                    @click="$dispatch('open-delete-modal', { id: '{{ $alat->hashid }}', name: '{{ $alat->nama_alat }} ({{ $alat->kapasitas }}/{{ $alat->kelas }})' })"
                                                    class="p-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition"
                                                    title="Hapus"><i class="fas fa-trash text-xs"></i></button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16 text-gray-400">
                    <i class="fas fa-box-open text-5xl mb-4 block"></i>Tidak ada data alat
                </div>
            @endforelse
        </div>

        {{-- Pagination dengan Nama Alat --}}
        <div class="mt-10 flex justify-center">
            {{ $alats->withQueryString()->links('vendor.pagination.alat-nama') }}
        </div>
    </div>

    {{-- Global Delete Modal --}}
    <div x-data="{ show: false, id: '', name: '' }"
        @open-delete-modal.window="show = true; id = $event.detail.id; name = $event.detail.name;" x-show="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" x-transition>
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 max-w-sm mx-4 shadow-2xl" @click.away="show = false">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Konfirmasi Hapus</h3>
            <p class="text-gray-600 mb-6">Yakin ingin menghapus alat <strong x-text="name"></strong>?</p>
            <div class="flex justify-end gap-3">
                <button @click="show = false"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl transition">Batal</button>
                <form :action="'{{ route('alats.destroy', '__ID__') }}'.replace('__ID__', id)" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition">Hapus</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Global Quick View Modal --}}
    <div x-data="{ show: false, url: '', name: '' }" @quick-view.window="show = true; url = $event.detail.url; name = $event.detail.name;"
        x-show="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70"
        @click.away="show = false" x-transition>
        <div class="relative max-w-3xl mx-4">
            <button @click="show = false"
                class="absolute -top-4 -right-4 w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center text-gray-600 hover:text-gray-800 transition">
                <i class="fas fa-times"></i>
            </button>
            <img :src="url" :alt="name"
                class="max-w-full max-h-[80vh] object-contain rounded-2xl shadow-2xl" x-show="url">
            <div class="mt-4 text-center text-white font-semibold" x-text="name"></div>
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
@endsection
