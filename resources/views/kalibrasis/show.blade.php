@extends('layouts.app')

@section('title', 'Detail Kalibrasi')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div
            class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-3xl shadow-xl border border-amber-100 overflow-hidden">
            <div
                class="bg-gradient-to-r from-amber-50 to-yellow-50 p-6 flex justify-between items-center border-b border-amber-100">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                    <i class="fas fa-wrench text-amber-500"></i> Detail Kalibrasi
                </h2>
                <a href="{{ route('kalibrasis.index') }}"
                    class="text-gray-500 dark:text-gray-400 hover:text-gray-700 flex items-center gap-1">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="p-6 md:p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <span class="text-gray-400 text-sm">Alat</span>
                        <span class="block font-semibold text-gray-800">{{ $data->alat->nama_alat ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 text-sm">Merk / Tipe</span>
                        <span class="block font-semibold text-gray-800">{{ $data->alat->merk ?? '-' }} /
                            {{ $data->alat->tipe ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 text-sm">Tanggal Kalibrasi</span>
                        <span
                            class="block font-semibold text-gray-800">{{ \Carbon\Carbon::parse($data->tanggal_kalibrasi)->format('d M Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 text-sm">Masa Berlaku Baru</span>
                        <span
                            class="block font-semibold text-gray-800">{{ \Carbon\Carbon::parse($data->masa_berlaku_baru)->format('d M Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 text-sm">No Sertifikat</span>
                        <span class="block font-semibold text-gray-800">{{ $data->no_sertifikat ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 text-sm">Status</span>
                        @php $expired = \Carbon\Carbon::parse($data->masa_berlaku_baru)->isPast(); @endphp
                        <span
                            class="inline-block px-3 py-1 text-xs font-semibold rounded-full mt-1
                        @if ($expired) bg-red-100 text-red-700
                        @else bg-emerald-100 text-emerald-700 @endif">
                            {{ $expired ? 'Expired' : 'Aktif' }}
                        </span>
                    </div>
                </div>

                @if ($data->keterangan)
                    <div class="border-t pt-6 mt-6">
                        <h3 class="font-semibold text-gray-800 mb-2">Keterangan</h3>
                        <p class="text-gray-600">{{ $data->keterangan }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
