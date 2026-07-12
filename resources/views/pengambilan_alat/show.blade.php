@extends('layouts.app')

@section('title', 'Detail Pengambilan Alat')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8" x-data="{ imgModal: false, imgSrc: '' }">

        {{-- Back Button --}}
        <a href="{{ route('pengambilan_alat.index') }}"
            class="inline-flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 mb-6 group transition-colors">
            <span
                class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 group-hover:bg-gray-200 flex items-center justify-center transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </span>
            <span class="font-medium">Kembali ke Daftar</span>
        </a>

        <div
            class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            {{-- Header Card --}}
            <div
                class="relative bg-gradient-to-br from-amber-50 via-orange-50 to-yellow-50 dark:from-amber-900/20 dark:via-orange-900/20 dark:to-yellow-900/20 p-6 sm:p-8 border-b border-amber-100 dark:border-gray-700">
                <div
                    class="absolute top-0 right-0 w-40 h-40 bg-amber-200/30 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl">
                </div>
                <div class="relative flex items-center gap-4">
                    <span
                        class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white dark:bg-gray-800 shadow-lg shadow-amber-100">
                        <i class="fas fa-info-circle text-2xl text-amber-500"></i>
                    </span>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Detail Pengambilan Alat</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">Informasi lengkap peminjaman alat</p>
                    </div>
                </div>

                {{-- Status Badge --}}
                <div class="relative mt-4 flex flex-wrap gap-2">
                    @if ($data->status == 'dipinjam')
                        <span
                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-xl text-sm font-semibold ring-1 ring-red-200 dark:ring-red-700">
                            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                            Sedang Dipinjam
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-xl text-sm font-semibold ring-1 ring-emerald-200 dark:ring-emerald-700">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Sudah Dikembalikan
                        </span>
                    @endif

                    @if ($data->status == 'dipinjam' && $data->lama_pinjam)
                        @php $statusPinjam = $data->status_pinjam; @endphp
                        @if ($statusPinjam === 'aman')
                            <span
                                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-xl text-sm font-semibold ring-1 ring-emerald-200 dark:ring-emerald-700">
                                <i class="fas fa-check-circle"></i> {{ $data->sisa_hari_label }}
                            </span>
                        @elseif ($statusPinjam === 'warning')
                            <span
                                class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 rounded-xl text-sm font-semibold ring-1 ring-yellow-200 dark:ring-yellow-700 animate-pulse">
                                <i class="fas fa-exclamation-triangle"></i> {{ $data->sisa_hari_label }}
                            </span>
                        @elseif ($statusPinjam === 'terlambat')
                            <span
                                class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-xl text-sm font-semibold ring-1 ring-red-200 dark:ring-red-700 animate-pulse">
                                <i class="fas fa-times-circle"></i> {{ $data->sisa_hari_label }}
                            </span>
                        @endif
                    @endif

                    @if ($data->alat)
                        @if ($data->alat->status == 'expired')
                            <span
                                class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-xl text-sm font-semibold ring-1 ring-red-200 dark:ring-red-700">
                                <i class="fas fa-exclamation-circle"></i> Alat Expired
                            </span>
                        @elseif($data->alat->status == 'warning')
                            <span
                                class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 rounded-xl text-sm font-semibold ring-1 ring-yellow-200 dark:ring-yellow-700">
                                <i class="fas fa-exclamation-triangle"></i> Alat Warning
                            </span>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Content --}}
            <div class="p-6 sm:p-8 space-y-8">
                {{-- Foto Alat --}}
                @if ($data->alat && $data->alat->foto)
                    <div class="flex justify-center">
                        <div class="relative group cursor-pointer"
                            @click="imgSrc = '{{ $data->alat->foto_url }}'; imgModal = true">
                            <img src="{{ $data->alat->foto_url }}" alt="{{ $data->alat->nama_alat }}"
                                class="max-w-full max-h-80 object-contain rounded-2xl border shadow-md transition-transform duration-300 group-hover:scale-105">
                            <div class="absolute inset-0 rounded-2xl ring-1 ring-inset ring-black/5 pointer-events-none">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Info Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Card: Nama Alat --}}
                    <div
                        class="group bg-gray-50 dark:bg-gray-900 hover:bg-white dark:hover:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 hover:border-amber-200 hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3 mb-3">
                            <span
                                class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <i class="fas fa-tools text-amber-600 dark:text-amber-400 text-sm"></i>
                            </span>
                            <span
                                class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Alat</span>
                        </div>
                        <p class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ $data->alat->nama_alat ?? '-' }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $data->alat->merk ?? '' }}
                            {{ $data->alat->tipe ?? '' }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Seri: {{ $data->alat->no_seri ?? '-' }}
                        </p>
                    </div>

                    {{-- Card: Pengguna --}}
                    <div
                        class="group bg-gray-50 dark:bg-gray-900 hover:bg-white dark:hover:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 hover:border-amber-200 hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3 mb-3">
                            <span
                                class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <i class="fas fa-user text-amber-600 dark:text-amber-400 text-sm"></i>
                            </span>
                            <span
                                class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Pengguna
                                & Bagian</span>
                        </div>
                        <p class="font-semibold text-gray-800 dark:text-gray-100">
                            {{ $data->nama_peminjam ?? ($data->user->name ?? '-') }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $data->bagian->nama ?? '-' }}</p>
                    </div>

                    {{-- Card: Jumlah --}}
                    <div
                        class="group bg-gray-50 dark:bg-gray-900 hover:bg-white dark:hover:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 hover:border-amber-200 hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3 mb-3">
                            <span
                                class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <i class="fas fa-boxes text-amber-600 dark:text-amber-400 text-sm"></i>
                            </span>
                            <span
                                class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Jumlah</span>
                        </div>
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $data->jumlah }} <span
                                class="text-lg text-gray-500 dark:text-gray-400">{{ $data->satuan }}</span></p>
                    </div>

                    {{-- Card: Waktu --}}
                    <div
                        class="group bg-gray-50 dark:bg-gray-900 hover:bg-white dark:hover:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 hover:border-amber-200 hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3 mb-3">
                            <span
                                class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-amber-600 dark:text-amber-400 text-sm"></i>
                            </span>
                            <span
                                class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Waktu
                                Pengambilan</span>
                        </div>
                        <p class="font-medium text-gray-800 dark:text-gray-100">
                            {{ \Carbon\Carbon::parse($data->waktu_pengambilan)->format('d M Y, H:i') }}
                        </p>
                    </div>

                    {{-- Card: Lama Pinjam & Jatuh Tempo --}}
                    @if ($data->lama_pinjam)
                        <div
                            class="group bg-gray-50 dark:bg-gray-900 hover:bg-white dark:hover:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 hover:border-amber-200 hover:shadow-md transition-all duration-300">
                            <div class="flex items-center gap-3 mb-3">
                                <span
                                    class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                    <i class="fas fa-hourglass-half text-amber-600 dark:text-amber-400 text-sm"></i>
                                </span>
                                <span
                                    class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Lama
                                    Pinjam</span>
                            </div>
                            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $data->lama_pinjam }} <span
                                    class="text-lg text-gray-500 dark:text-gray-400">hari</span></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Jatuh Tempo: <span
                                    class="font-semibold text-gray-700 dark:text-gray-200">{{ $data->tanggal_jatuh_tempo?->format('d M Y') ?? '-' }}</span>
                            </p>
                        </div>
                    @endif

                    {{-- Card: Keperluan --}}
                    <div
                        class="md:col-span-2 group bg-gray-50 dark:bg-gray-900 hover:bg-white dark:hover:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 hover:border-amber-200 hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3 mb-3">
                            <span
                                class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <i class="fas fa-clipboard-list text-amber-600 dark:text-amber-400 text-sm"></i>
                            </span>
                            <span
                                class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Keperluan</span>
                        </div>
                        <p class="text-gray-800 dark:text-gray-100">{{ $data->keperluan ?? '-' }}</p>
                    </div>
                </div>

                {{-- Foto Bukti (DENGAN LIGHTBOX) --}}
                @if ($data->foto)
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-8">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <span
                                class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <i class="fas fa-camera text-amber-600 dark:text-amber-400 text-sm"></i>
                            </span>
                            Foto Bukti Pengambilan
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
                    @if ($data->status == 'dipinjam')
                        <a href="{{ route('pengembalian_alat.create', $data->hashid) }}"
                            class="flex-1 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white font-semibold py-4 px-6 rounded-2xl transition-all duration-300 shadow-lg shadow-emerald-200 hover:shadow-xl hover:shadow-emerald-300 hover:-translate-y-0.5">
                            <i class="fas fa-undo-alt"></i>
                            Kembalikan Alat
                        </a>
                    @endif

                    @if ($data->alat && in_array($data->alat->status, ['expired', 'warning']))
                        <a href="{{ route('kalibrasis.create', $data->alat->hashid) }}"
                            class="flex-1 inline-flex items-center justify-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-4 px-6 rounded-2xl transition-all duration-300 shadow-lg shadow-yellow-200 hover:shadow-xl hover:shadow-yellow-300 hover:-translate-y-0.5">
                            <i class="fas fa-tools"></i>
                            Kalibrasi Alat
                        </a>
                    @endif

                    <a href="{{ route('pengambilan_alat.export.single', $data->hashid) }}" target="_blank"
                        class="flex-1 inline-flex items-center justify-center gap-2 bg-white dark:bg-gray-800 border-2 border-amber-200 dark:border-amber-700 hover:bg-amber-50 dark:hover:bg-gray-700 text-amber-700 dark:text-amber-400 font-semibold py-4 px-6 rounded-2xl transition-all duration-300 hover:border-amber-300">
                        <i class="fas fa-print"></i>
                        Cetak Bukti
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
                    class="absolute -top-3 -right-3 w-10 h-10 bg-white dark:bg-gray-800 rounded-full shadow-lg flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white transition z-10">
                    <i class="fas fa-times"></i>
                </button>
                <img :src="imgSrc" alt="Preview"
                    class="w-full h-full object-contain rounded-2xl shadow-2xl max-w-none max-h-none">
            </div>
        </div>
    </div>
@endsection
