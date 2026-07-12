@extends('layouts.app')

@section('title', 'Detail Kategori')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div
            class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-3xl shadow-xl border border-amber-100 overflow-hidden">
            {{-- Header --}}
            <div
                class="bg-gradient-to-r from-amber-50 to-yellow-50 p-6 flex justify-between items-center border-b border-amber-100">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center text-amber-600">
                        <i class="fas fa-tag text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">{{ $kategori->nama }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Detail Kategori</p>
                    </div>
                </div>
                <a href="{{ route('kategoris.index') }}"
                    class="text-gray-500 dark:text-gray-400 hover:text-gray-700 flex items-center gap-1 transition">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            {{-- Body --}}
            <div class="p-6 md:p-8 space-y-8">
                {{-- Informasi Utama --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 rounded-2xl p-5">
                        <span class="text-gray-400 text-sm">Nama Kategori</span>
                        <p class="text-xl font-semibold text-gray-800 mt-1">{{ $kategori->nama }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 rounded-2xl p-5">
                        <span class="text-gray-400 text-sm">Keterangan</span>
                        <p class="text-gray-700 mt-1 leading-relaxed">{{ $kategori->keterangan ?: 'Tidak ada keterangan' }}
                        </p>
                    </div>
                </div>

                {{-- Statistik (jika relasi tersedia) --}}
                @if (method_exists($kategori, 'alats') || method_exists($kategori, 'spareparts'))
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @if (method_exists($kategori, 'alats'))
                            <div class="bg-amber-50 rounded-2xl p-5 border border-amber-100 flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600">
                                    <i class="fas fa-tools text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Jumlah Alat</p>
                                    <p class="text-2xl font-bold text-gray-800">{{ $kategori->alats->count() }}</p>
                                </div>
                            </div>
                        @endif
                        @if (method_exists($kategori, 'spareparts'))
                            <div class="bg-amber-50 rounded-2xl p-5 border border-amber-100 flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600">
                                    <i class="fas fa-boxes text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Jumlah Sparepart</p>
                                    <p class="text-2xl font-bold text-gray-800">{{ $kategori->spareparts->count() }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Daftar Alat dalam Kategori (opsional) --}}
                @if (method_exists($kategori, 'alats') && $kategori->alats->isNotEmpty())
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-tools text-amber-500"></i> Alat dalam Kategori Ini
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach ($kategori->alats->take(6) as $alat)
                                <a href="{{ route('alats.show', $alat->hashid) }}"
                                    class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 hover:bg-amber-50 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-medium text-gray-700 transition">
                                    {{ $alat->nama_alat }}
                                </a>
                            @endforeach
                        </div>
                        @if ($kategori->alats->count() > 6)
                            <p class="text-sm text-amber-600 mt-2">...dan {{ $kategori->alats->count() - 6 }} alat lainnya
                            </p>
                        @endif
                    </div>
                @endif

                {{-- Tombol Aksi --}}
                <div class="flex gap-4 pt-4">
                    <a href="{{ route('kategoris.edit', $kategori->hashid) }}"
                        class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-semibold py-4 rounded-2xl transition flex items-center justify-center gap-2 shadow-md shadow-amber-200">
                        <i class="fas fa-edit"></i> Edit Kategori
                    </a>
                    <a href="{{ route('kategoris.index') }}"
                        class="flex-1 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-gray-700 font-semibold py-4 rounded-2xl transition text-center">
                        Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
