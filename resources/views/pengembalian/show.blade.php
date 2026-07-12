@extends('layouts.app')

@section('title', 'Detail Pengembalian Sparepart')

@section('content')
    <main class="p-6 flex flex-col items-center space-y-8">
        <section
            class="w-full max-w-4xl bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-lg shadow-lg p-8">

            <h1 class="text-3xl font-bold text-gray-800 mb-8 border-b pb-4 flex items-center gap-3">
                <i class="fas fa-undo-alt text-green-600"></i>
                Detail Pengembalian Sparepart
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-sm text-gray-700">

                <div>
                    <p class="font-medium text-gray-500 dark:text-gray-400 mb-1">User</p>
                    <p class="text-lg font-semibold">{{ $pengembalian->user->name ?? '-' }}</p>
                </div>

                <div>
                    <p class="font-medium text-gray-500 dark:text-gray-400 mb-1">Bagian</p>
                    <p class="text-lg">{{ $pengembalian->bagian->nama ?? '-' }}</p>
                </div>

                <div>
                    <p class="font-medium text-gray-500 dark:text-gray-400 mb-1">Sparepart</p>
                    <p class="text-lg font-semibold">{{ $pengembalian->sparepart->nama_part ?? '-' }}</p>
                </div>

                <div>
                    <p class="font-medium text-gray-500 dark:text-gray-400 mb-1">Jumlah Dikembalikan</p>
                    <p class="text-2xl font-bold text-green-600">{{ $pengembalian->jumlah_dikembalikan }}</p>
                </div>

                <div>
                    <p class="font-medium text-gray-500 dark:text-gray-400 mb-1">Kondisi Barang</p>
                    <span
                        class="inline-flex items-center px-5 py-2 rounded-full text-sm font-medium
                        {{ $pengembalian->kondisi == 'baik' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        <i class="fas fa-circle text-xs mr-2"></i>
                        {{ ucfirst($pengembalian->kondisi) }}
                    </span>
                </div>

                <div class="md:col-span-2">
                    <p class="font-medium text-gray-500 dark:text-gray-400 mb-1">Alasan Pengembalian</p>
                    <p class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 p-4 rounded-xl border">
                        {{ $pengembalian->alasan }}</p>
                </div>

                @if ($pengembalian->keterangan)
                    <div class="md:col-span-2">
                        <p class="font-medium text-gray-500 dark:text-gray-400 mb-1">Keterangan Tambahan</p>
                        <p class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 p-4 rounded-xl border">
                            {{ $pengembalian->keterangan }}</p>
                    </div>
                @endif

                <div>
                    <p class="font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal Kembali</p>
                    <p class="text-lg">{{ $pengembalian->tanggal_kembali?->format('d F Y H:i') ?? '-' }}</p>
                </div>

                <div>
                    <p class="font-medium text-gray-500 dark:text-gray-400 mb-1">Pengambilan Asal</p>
                    <p class="text-lg">
                        {{ $pengembalian->pengambilan->sparepart->nama_part ?? '-' }}
                        (Diambil: {{ $pengembalian->pengambilan->jumlah ?? 0 }})
                    </p>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="mt-10 flex flex-wrap gap-4 justify-end">
                <a href="{{ route('pengembalian.edit', $pengembalian->hashid) }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-medium flex items-center gap-2 transition">
                    <i class="fas fa-edit"></i> Edit Pengembalian
                </a>

                <a href="{{ route('pengembalian.index') }}"
                    class="bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-medium flex items-center gap-2 transition">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>

        </section>
    </main>
@endsection
