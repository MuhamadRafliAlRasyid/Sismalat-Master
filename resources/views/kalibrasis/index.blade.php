@extends('layouts.app')

@section('title', 'Data Kalibrasi Alat')

@section('content')
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <i class="fas fa-wrench text-amber-500"></i> Kalibrasi Alat
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Riwayat kalibrasi alat ukur dan instrumen</p>
            </div>
        </div>

        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)"
                class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-3 rounded-xl flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        <form method="GET" class="mb-8">
            <div class="relative max-w-md">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama alat..."
                    class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 pl-11 bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition">
                <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
            </div>
        </form>

        {{-- Daftar Kartu Kalibrasi --}}
        <div class="space-y-4">
            @forelse($data as $i => $item)
                @php
                    $tanggalKalibrasi = \Carbon\Carbon::parse($item->tanggal_kalibrasi);
                    $masaBerlaku = \Carbon\Carbon::parse($item->masa_berlaku_baru);
                    $expired = $masaBerlaku->isPast();
                    $warning = !$expired && $masaBerlaku->diffInDays(now()) <= 30; // peringatan 30 hari sebelum expired
                    $alat = $item->alat;
                @endphp

                <div
                    class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-2xl shadow-md border transition-all duration-300 hover:shadow-xl group
                @if ($expired) border-red-200 hover:border-red-300
                @elseif($warning) border-yellow-200 hover:border-yellow-300
                @else border-amber-100 hover:border-amber-300 @endif">

                    <div class="p-5 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            {{-- Indikator Status (kiri) --}}
                            <div class="flex-shrink-0">
                                <div
                                    class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl
                                @if ($expired) bg-red-100 text-red-500
                                @elseif($warning) bg-yellow-100 text-yellow-600
                                @else bg-emerald-100 text-emerald-500 @endif">
                                    @if ($expired)
                                        <i class="fas fa-exclamation-circle"></i>
                                    @elseif($warning)
                                        <i class="fas fa-clock"></i>
                                    @else
                                        <i class="fas fa-check-circle"></i>
                                    @endif
                                </div>
                            </div>

                            {{-- Informasi Utama --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-1">
                                    <h3 class="font-bold text-gray-800 text-lg truncate">
                                        {{ $alat->nama_alat ?? 'Alat tidak ditemukan' }}
                                    </h3>
                                    <span
                                        class="px-2.5 py-0.5 text-xs font-semibold rounded-full self-start
                                    @if ($expired) bg-red-100 text-red-700
                                    @elseif($warning) bg-yellow-100 text-yellow-700
                                    @else bg-emerald-100 text-emerald-700 @endif">
                                        @if ($expired)
                                            Expired
                                        @elseif($warning)
                                            Segera Expired
                                        @else
                                            Aktif
                                        @endif
                                    </span>
                                </div>

                                @if ($alat)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                        {{ $alat->merk ?? '' }} {{ $alat->tipe ? '• ' . $alat->tipe : '' }}
                                        @if ($alat->no_seri)
                                            <span class="ml-2 text-gray-400">#{{ $alat->no_seri }}</span>
                                        @endif
                                    </p>
                                @endif

                                {{-- Detail Tanggal --}}
                                <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm mt-3">
                                    <div class="flex items-center gap-1.5 text-gray-600">
                                        <i class="fas fa-calendar-check text-amber-400 w-4"></i>
                                        <span class="text-gray-500 dark:text-gray-400">Kalibrasi:</span>
                                        <span class="font-medium">{{ $tanggalKalibrasi->format('d M Y') }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-gray-600">
                                        <i class="fas fa-calendar-alt text-amber-400 w-4"></i>
                                        <span class="text-gray-500 dark:text-gray-400">Berlaku s/d:</span>
                                        <span
                                            class="font-medium {{ $expired ? 'text-red-600' : '' }}">{{ $masaBerlaku->format('d M Y') }}</span>
                                    </div>
                                    @if ($item->no_sertifikat)
                                        <div class="flex items-center gap-1.5 text-gray-600">
                                            <i class="fas fa-certificate text-amber-400 w-4"></i>
                                            <span class="text-gray-500 dark:text-gray-400">Sertifikat:</span>
                                            <span class="font-medium">{{ $item->no_sertifikat }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Progress Masa Berlaku --}}
                                @php
                                    $totalHari = $tanggalKalibrasi->diffInDays($masaBerlaku);
                                    $hariBerlalu = $tanggalKalibrasi->diffInDays(now());
                                    $persen =
                                        $totalHari > 0 ? min(100, max(0, ($hariBerlalu / $totalHari) * 100)) : 100;
                                @endphp
                                <div class="mt-3 w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500
                                    @if ($expired) bg-red-400
                                    @elseif($warning) bg-yellow-400
                                    @else bg-amber-400 @endif"
                                        style="width: {{ $persen }}%"></div>
                                </div>
                            </div>

                            {{-- Aksi --}}
                            <div class="flex sm:flex-col gap-2 sm:items-end mt-4 sm:mt-0">
                                <a href="{{ route('kalibrasis.show', $item->hashid) }}"
                                    class="p-2.5 bg-amber-100 text-amber-700 rounded-xl hover:bg-amber-200 transition text-center"
                                    title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('kalibrasis.edit', $item->hashid) }}"
                                    class="p-2.5 bg-blue-100 text-blue-700 rounded-xl hover:bg-blue-200 transition text-center"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('kalibrasis.destroy', $item->hashid) }}"
                                    onsubmit="return confirm('Yakin hapus data ini?')">
                                    @csrf @method('DELETE')
                                    <button class="p-2.5 bg-red-100 text-red-700 rounded-xl hover:bg-red-200 transition"
                                        title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-20 text-gray-400">
                    <i class="fas fa-wrench text-6xl mb-4 block"></i>
                    <p class="text-lg font-medium">Tidak ada data kalibrasi</p>
                    <p class="text-sm mt-1">Data kalibrasi akan muncul di sini setelah ditambahkan</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8 flex justify-center">
            {{ $data->links() }}
        </div>
    </div>
@endsection
