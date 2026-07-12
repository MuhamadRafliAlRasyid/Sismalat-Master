@extends('layouts.app')

@section('title', 'Detail Alat')

@section('content')
    <div class="max-w-4xl mx-auto px-4" x-data="{ imgModal: false }">
        <div
            class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-3xl shadow-xl border border-amber-100 overflow-hidden">
            <div
                class="bg-gradient-to-r from-amber-50 to-yellow-50 p-6 flex justify-between items-center border-b border-amber-100">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                    <i class="fas fa-tools text-amber-500"></i> {{ $alat->nama_alat }}
                </h2>
                <a href="{{ route('alats.index') }}"
                    class="text-gray-500 dark:text-gray-200 hover:text-gray-700 dark:hover:text-white flex items-center gap-1 transition">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="p-6 md:p-8 space-y-6">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">ID: {{ $alat->hashid }}</span>
                    @if ($alat->status == 'expired')
                        <span
                            class="px-4 py-1 bg-red-100 text-red-700 rounded-full text-sm font-semibold animate-pulse">Expired</span>
                    @elseif($alat->status == 'warning')
                        <span
                            class="px-4 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-semibold">Warning</span>
                    @else
                        <span class="px-4 py-1 bg-emerald-100 text-emerald-700 rounded-full text-sm font-semibold">OK</span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div><span class="text-gray-400 text-sm">Nama Alat</span><span
                            class="block font-semibold text-gray-800">{{ $alat->nama_alat }}</span></div>
                    <div><span class="text-gray-400 text-sm">Kelas</span><span
                            class="block font-semibold text-gray-800">{{ $alat->kelas ?: '-' }}</span></div>
                    <div><span class="text-gray-400 text-sm">Merk</span><span
                            class="block font-semibold text-gray-800">{{ $alat->merk }}</span></div>
                    <div><span class="text-gray-400 text-sm">Tipe</span><span
                            class="block font-semibold text-gray-800">{{ $alat->tipe }}</span></div>
                    <div><span class="text-gray-400 text-sm">No Seri</span><span
                            class="block font-semibold text-gray-800">{{ $alat->no_seri ?: '-' }}</span></div>
                    <div><span class="text-gray-400 text-sm">No Identitas</span><span
                            class="block font-semibold text-gray-800">{{ $alat->no_identitas ?: '-' }}</span></div>
                    <div><span class="text-gray-400 text-sm">Kapasitas</span><span
                            class="block font-semibold text-gray-800">{{ $alat->kapasitas ?: '-' }}</span></div>
                    <div><span class="text-gray-400 text-sm">Daya Baca</span><span
                            class="block font-semibold text-gray-800">{{ $alat->daya_baca ?: '-' }}</span></div>
                    <div><span class="text-gray-400 text-sm">Jumlah</span><span
                            class="block font-semibold text-gray-800">{{ $alat->jumlah ?? 1 }}</span></div>
                    <div><span class="text-gray-400 text-sm">Kategori</span><span
                            class="block font-semibold text-gray-800">{{ $alat->kategori->nama ?? '-' }}</span></div>
                    <div><span class="text-gray-400 text-sm">No Sertifikat</span><span
                            class="block font-semibold text-gray-800">{{ $alat->no_sertifikat ?: '-' }}</span></div>
                    <div><span class="text-gray-400 text-sm">Masa Berlaku</span><span
                            class="block font-semibold text-gray-800">{{ $alat->masa_berlaku ?: '-' }}</span></div>
                </div>

                {{-- Foto Alat dengan Lightbox --}}
                @if ($alat->foto)
                    <div class="border-t pt-6 mt-6">
                        <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-camera text-amber-500"></i> Foto Alat
                        </h3>
                        <div class="flex justify-center">
                            <img src="{{ $alat->foto_url }}" alt="Foto {{ $alat->nama_alat }}" @click="imgModal = true"
                                class="max-w-full max-h-80 object-contain rounded-2xl border shadow-sm cursor-pointer hover:opacity-90 transition">
                        </div>
                    </div>
                @endif

                {{-- QR Code --}}
                @if ($alat->qr_code)
                    <div class="border-t pt-6 mt-6">
                        <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-qrcode text-amber-500"></i> QR Code
                        </h3>
                        <div class="flex justify-center">
                            <img src="{{ asset('storage/' . $alat->qr_code) }}" alt="QR Code"
                                class="w-40 h-40 object-contain rounded-2xl border shadow-sm">
                        </div>
                    </div>
                @endif

                {{-- Riwayat Kalibrasi dengan Timeline --}}
                <div class="border-t pt-6 mt-6">
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-history text-amber-500"></i> Riwayat Kalibrasi
                    </h3>
                    @if ($alat->kalibrasis->count() > 0)
                        <div class="space-y-4">
                            @foreach ($alat->kalibrasis as $k)
                                <div class="flex gap-4">
                                    <div class="flex flex-col items-center">
                                        <div class="w-3 h-3 bg-amber-400 rounded-full"></div>
                                        <div class="w-0.5 h-full bg-amber-200"></div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 p-4 rounded-2xl border flex-1">
                                        <div class="flex justify-between items-start">
                                            <span class="font-medium text-gray-700">{{ $k->tanggal_kalibrasi }}</span>
                                            <span
                                                class="text-sm bg-amber-100 text-amber-700 px-3 py-1 rounded-full">{{ $k->no_sertifikat }}</span>
                                        </div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Masa Berlaku Baru: <span
                                                class="font-semibold">{{ $k->masa_berlaku_baru }}</span></p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-400 italic">Belum ada riwayat kalibrasi</p>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="border-t border-gray-100 dark:border-gray-700 pt-8 flex flex-wrap gap-4">

                    {{-- Kalibrasi jika expired/warning --}}
                    @if (in_array($alat->status, ['expired', 'warning']))
                        <a href="{{ route('kalibrasis.create', $alat->hashid) }}"
                            class="flex-1 min-w-[150px] inline-flex items-center justify-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-4 px-6 rounded-2xl transition-all duration-300 shadow-lg shadow-yellow-200 hover:shadow-xl hover:shadow-yellow-300 hover:-translate-y-0.5">
                            <i class="fas fa-tools"></i>
                            Kalibrasi Alat
                        </a>
                    @endif

                    {{-- Edit & Hapus hanya untuk admin/super --}}
                    @if (in_array(auth()->user()->role, ['admin', 'super']))
                        <a href="{{ route('alats.edit', $alat->hashid) }}"
                            class="flex-1 min-w-[150px] inline-flex items-center justify-center gap-2 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-4 px-6 rounded-2xl transition-all duration-300 shadow-lg shadow-blue-200 hover:shadow-xl hover:shadow-blue-300 hover:-translate-y-0.5">
                            <i class="fas fa-edit"></i> Edit Alat
                        </a>
                        <form method="POST" action="{{ route('alats.destroy', $alat->hashid) }}"
                            class="flex-1 min-w-[150px]" onsubmit="return confirm('Yakin ingin menghapus alat ini?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 bg-red-500 hover:bg-red-600 text-white font-semibold py-4 px-6 rounded-2xl transition-all duration-300 shadow-lg shadow-red-200 hover:shadow-xl hover:shadow-red-300 hover:-translate-y-0.5">
                                <i class="fas fa-trash"></i> Hapus Alat
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Lightbox Modal --}}
        <div x-show="imgModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80"
            @click="imgModal = false">
            <img src="{{ $alat->foto_url }}" class="max-w-[90%] max-h-[90vh] object-contain rounded-2xl shadow-2xl">
        </div>
    </div>
@endsection
