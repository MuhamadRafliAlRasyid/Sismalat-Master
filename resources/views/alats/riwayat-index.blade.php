@extends('layouts.app')

@section('title', 'Alat dengan Riwayat')

@section('content')
    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8" x-data="{
        search: '',
        status: '',
        init() {
            const params = new URLSearchParams(window.location.search);
            this.status = params.get('status') || '';
            this.search = params.get('search') || '';
        },
        filterByStatus() {
            const url = new URL(window.location);
            if (this.status) {
                url.searchParams.set('status', this.status);
            } else {
                url.searchParams.delete('status');
            }
            // Pertahankan search yang sudah ada, jangan dihapus
            if (this.search) {
                url.searchParams.set('search', this.search);
            }
            url.searchParams.delete('page');
            window.location = url.toString();
        },
        searchAlat() {
            const url = new URL(window.location);
            if (this.search) {
                url.searchParams.set('search', this.search);
            } else {
                url.searchParams.delete('search');
            }
            // Pertahankan status yang sudah dipilih
            if (this.status) {
                url.searchParams.set('status', this.status);
            }
            url.searchParams.delete('page');
            window.location = url.toString();
        }
    }">

        <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
            <i class="fas fa-clipboard-list text-amber-500"></i> Alat yang Memiliki Riwayat
        </h1>

        {{-- Pencarian & Filter --}}
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <div class="relative flex-1">
                <input type="text" x-model="search" @keyup.enter="searchAlat()" placeholder="Cari nama alat..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-amber-300 focus:border-amber-400 transition bg-white dark:bg-gray-700 text-gray-800 dark:text-white">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
            <div class="w-full sm:w-48">
                <select x-model="status" @change="filterByStatus()"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-amber-300 focus:border-amber-400 transition bg-white dark:bg-gray-700 text-gray-800 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="dipinjam">Dipinjam</option>
                    <option value="dikembalikan">Dikembalikan</option>
                    <option value="dikalibrasi">Dikalibrasi</option>
                </select>
            </div>
        </div>

        {{-- Konten --}}
        @if ($alats->isEmpty())
            <div class="text-center py-16 text-gray-500 dark:text-gray-400">
                <i class="fas fa-box-open text-5xl mb-4 text-gray-300"></i>
                <p>Belum ada alat yang memiliki riwayat</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($alats as $alat)
                    @php
                        // Gunakan collection yang sudah di-eager load
                        $pengambilanCollection = $alat->pengambilan;
                        $pengembalianCollection = $alat->pengembalian; // hasManyThrough
                        $kalibrasiCollection = $alat->kalibrasis;

                        $hasPengambilan = $pengambilanCollection->isNotEmpty();
                        $hasPengembalian = $pengembalianCollection->isNotEmpty();
                        $isDikalibrasi = $kalibrasiCollection->isNotEmpty();

                        // Tentukan status peminjaman
                        if ($hasPengembalian) {
                            // Jika ada pengembalian, anggap alat sudah dikembalikan
                            $statusPinjam = 'dikembalikan';
                        } elseif ($hasPengambilan) {
                            // Tidak ada pengembalian, cek apakah masih ada yang dipinjam
                            $statusPinjam = $pengambilanCollection->contains('status', 'dipinjam')
                                ? 'dipinjam'
                                : 'dikembalikan';
                        } else {
                            $statusPinjam = null;
                        }
                    @endphp

                    <a href="{{ route('alats.riwayat', $alat->hashid) }}"
                        class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-amber-100 dark:border-gray-700 hover:shadow-md hover:border-amber-300 dark:hover:border-amber-500 transition-all duration-300 p-6 block transform hover:-translate-y-1 animate-fade-in-up"
                        style="animation-delay: {{ $loop->index * 0.1 }}s">

                        {{-- Foto / Ikon --}}
                        <div
                            class="flex items-center justify-center w-16 h-16 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-2xl mx-auto mb-4 text-3xl overflow-hidden relative group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-tools"></i>
                            @if ($alat->foto)
                                <img src="{{ $alat->foto_url }}" alt="{{ $alat->nama_alat }}"
                                    class="absolute inset-0 w-full h-full object-cover rounded-2xl"
                                    onerror="this.style.display='none';">
                            @endif
                        </div>

                        <h3
                            class="text-lg font-semibold text-gray-800 dark:text-white text-center group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                            {{ $alat->nama_alat }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center mt-1">{{ $alat->merk }}</p>

                        {{-- Badge --}}
                        <div class="flex justify-center mt-3 flex-wrap gap-1">
                            {{-- 1. Badge "Dipinjam" – hanya jika ada pengambilan yang statusnya 'dipinjam' --}}
                            @if ($alat->pengambilan->contains('status', 'dipinjam'))
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-medium cursor-default
            bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300"
                                    title="Sedang dipinjam">
                                    Dipinjam
                                </span>
                            @endif

                            {{-- 2. Badge "Dikembalikan" – hanya jika ada data pengembalian --}}
                            @if ($alat->pengembalian->isNotEmpty())
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-medium cursor-default
            bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300"
                                    title="Sudah ada pengembalian">
                                    Dikembalikan
                                </span>
                            @endif

                            {{-- 3. Badge "Terkalibrasi" --}}
                            @if ($alat->kalibrasis->isNotEmpty())
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-medium cursor-default
            bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300"
                                    title="Memiliki riwayat kalibrasi">
                                    Terkalibrasi
                                </span>
                            @endif

                            {{-- Fallback jika tidak ada riwayat sama sekali --}}
                            @if ($alat->pengambilan->isEmpty() && $alat->pengembalian->isEmpty() && $alat->kalibrasis->isEmpty())
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 cursor-default"
                                    title="Tidak ada transaksi">
                                    Tidak ada riwayat
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $alats->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }
    </style>
@endpush

@push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
