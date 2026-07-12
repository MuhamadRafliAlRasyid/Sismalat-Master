@extends('layouts.app')

@section('title', 'Detail Pengembalian Alat')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8" x-data="{ imgModal: false, imgSrc: '' }">

        {{-- Back Button --}}
        <a href="{{ route('pengembalian_alat.index') }}"
            class="inline-flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 mb-6 group transition-colors">
            <span
                class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 group-hover:bg-gray-200 flex items-center justify-center transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </span>
            <span class="font-medium">Kembali ke Daftar</span>
        </a>

        <div
            class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            {{-- Header --}}
            <div
                class="relative bg-gradient-to-br from-amber-50 via-orange-50 to-yellow-50 p-6 sm:p-8 border-b border-amber-100">
                <div
                    class="absolute top-0 right-0 w-40 h-40 bg-amber-200/30 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl">
                </div>
                <div class="relative flex items-center gap-4">
                    <span
                        class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 shadow-lg shadow-amber-100">
                        <i class="fas fa-info-circle text-2xl text-amber-500"></i>
                    </span>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Detail Pengembalian Alat</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">Informasi lengkap pengembalian alat</p>
                    </div>
                </div>

                {{-- Status Badge --}}
                <div class="relative mt-4 flex flex-wrap gap-2">
                    <span
                        class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 text-amber-700 rounded-xl text-sm font-semibold ring-1 ring-amber-200">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        Sudah Dikembalikan
                    </span>

                    {{-- Status Kalibrasi Alat --}}
                    @if ($data->pengambilan && $data->pengambilan->alat)
                        @if ($data->pengambilan->alat->status == 'expired')
                            <span
                                class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-700 rounded-xl text-sm font-semibold ring-1 ring-red-200">
                                <i class="fas fa-exclamation-circle"></i> Alat Expired
                            </span>
                        @elseif($data->pengambilan->alat->status == 'warning')
                            <span
                                class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-50 text-yellow-700 rounded-xl text-sm font-semibold ring-1 ring-yellow-200">
                                <i class="fas fa-exclamation-triangle"></i> Alat Warning
                            </span>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Content --}}
            <div class="p-6 sm:p-8 space-y-8">
                {{-- Foto Alat (DENGAN LIGHTBOX) --}}
                @if ($data->pengambilan && $data->pengambilan->alat && $data->pengambilan->alat->foto)
                    <div class="flex justify-center">
                        <div class="relative group cursor-pointer"
                            @click="imgSrc = '{{ $data->pengambilan->alat->foto_url }}'; imgModal = true">
                            <img src="{{ $data->pengambilan->alat->foto_url }}"
                                alt="{{ $data->pengambilan->alat->nama_alat }}"
                                class="max-w-full max-h-80 object-contain rounded-2xl border shadow-md transition-transform duration-300 group-hover:scale-105">
                            <div
                                class="absolute inset-0 bg-black/0 group-hover:bg-black/5 rounded-2xl transition-colors flex items-center justify-center">
                                <span
                                    class="text-white bg-black/50 px-3 py-1 rounded-full text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                    Klik untuk memperbesar
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Info Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        class="group bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 hover:bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 hover:border-amber-200 hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center">
                                <i class="fas fa-tools text-amber-600 text-sm"></i>
                            </span>
                            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Alat</span>
                        </div>
                        <p class="text-xl font-bold text-gray-800">{{ $data->pengambilan->alat->nama_alat ?? '-' }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $data->pengambilan->alat->merk ?? '' }}
                            {{ $data->pengambilan->alat->tipe ?? '' }}</p>
                    </div>
                    <div
                        class="group bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 hover:bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 hover:border-amber-200 hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center">
                                <i class="fas fa-user text-amber-600 text-sm"></i>
                            </span>
                            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Pengguna</span>
                        </div>
                        <p class="font-semibold text-gray-800">{{ $data->nama_peminjam ?? ($data->user->name ?? '-') }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Bagian:
                            {{ $data->pengambilan->bagian->nama ?? '-' }}</p>
                    </div>
                    <div
                        class="group bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 hover:bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 hover:border-amber-200 hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center">
                                <i class="fas fa-boxes text-amber-600 text-sm"></i>
                            </span>
                            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Jumlah</span>
                        </div>
                        <p class="text-3xl font-bold text-amber-600">{{ $data->jumlah }}</p>
                    </div>
                    <div
                        class="group bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 hover:bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 hover:border-amber-200 hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center">
                                <i class="fas fa-calendar-check text-amber-600 text-sm"></i>
                            </span>
                            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Tanggal</span>
                        </div>
                        <p class="font-semibold text-gray-800">
                            {{ \Carbon\Carbon::parse($data->tanggal_pengembalian)->format('d M Y') }}</p>
                    </div>
                    <div
                        class="md:col-span-2 group bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 hover:bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 hover:border-amber-200 hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center">
                                <i class="fas fa-sticky-note text-amber-600 text-sm"></i>
                            </span>
                            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Keterangan</span>
                        </div>
                        <p class="text-gray-800">{{ $data->keterangan ?? 'Tidak ada keterangan' }}</p>
                    </div>
                </div>

                {{-- Foto Bukti Pengembalian (JIKA ADA) --}}
                @if ($data->foto)
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center">
                                <i class="fas fa-camera text-amber-600 text-sm"></i>
                            </span>
                            Foto Bukti Pengembalian
                        </h3>
                        <div class="flex justify-center">
                            <div class="relative group cursor-pointer"
                                @click="imgSrc = '{{ $data->foto_url }}'; imgModal = true">
                                <img src="{{ $data->foto_url }}" alt="Foto bukti"
                                    class="max-w-full max-h-80 object-contain rounded-2xl border shadow-md transition-transform duration-300 group-hover:scale-105">
                                <div
                                    class="absolute inset-0 bg-black/0 group-hover:bg-black/5 rounded-2xl transition-colors flex items-center justify-center">
                                    <span
                                        class="text-white bg-black/50 px-3 py-1 rounded-full text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                        Klik untuk memperbesar
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div class="border-t border-gray-100 dark:border-gray-700 pt-8 flex flex-col sm:flex-row gap-4">
                    @if (in_array(auth()->user()->role, ['admin', 'super']))
                        <a href="{{ route('pengembalian_alat.edit', $data->hashid) }}"
                            class="flex-1 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold py-4 px-6 rounded-2xl transition-all duration-300 shadow-lg shadow-amber-200 hover:shadow-xl hover:-translate-y-0.5">
                            <i class="fas fa-edit"></i> Edit Data
                        </a>
                    @endif

                    {{-- Tombol Kalibrasi jika alat expired/warning --}}
                    @if (
                        $data->pengambilan &&
                            $data->pengambilan->alat &&
                            in_array($data->pengambilan->alat->status, ['expired', 'warning']))
                        <a href="{{ route('kalibrasi.create', $data->pengambilan->alat->hashid) }}"
                            class="flex-1 inline-flex items-center justify-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-4 px-6 rounded-2xl transition-all duration-300 shadow-lg shadow-yellow-200 hover:shadow-xl hover:shadow-yellow-300 hover:-translate-y-0.5">
                            <i class="fas fa-tools"></i>
                            Kalibrasi Alat
                        </a>
                    @endif

                    <a href="{{ route('pengembalian_alat.index') }}"
                        class="flex-1 inline-flex items-center justify-center gap-2 bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 hover:bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 text-gray-700 font-semibold py-4 px-6 rounded-2xl transition-all duration-300 hover:border-gray-300">
                        <i class="fas fa-list"></i> Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>

        {{-- Lightbox Modal --}}
        <div x-show="imgModal" x-transition.opacity.duration.300ms
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80 p-4"
            @click.away="imgModal = false; imgSrc = ''" x-cloak>
            <div class="relative max-w-5xl max-h-full">
                <button @click="imgModal = false; imgSrc = ''"
                    class="absolute -top-3 -right-3 w-10 h-10 bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-full shadow-lg flex items-center justify-center text-gray-600 hover:text-gray-800 transition z-10">
                    <i class="fas fa-times"></i>
                </button>
                <img :src="imgSrc" alt="Preview"
                    class="max-w-[90vw] max-h-[90vh] object-contain rounded-2xl shadow-2xl">
            </div>
        </div>
    </div>
@endsection
