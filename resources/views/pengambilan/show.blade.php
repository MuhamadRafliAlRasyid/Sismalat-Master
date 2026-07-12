@extends('layouts.app')

@section('title', 'Detail Pengambilan Sparepart')

@section('content')
    <main class="p-6 flex flex-col items-center space-y-8">
        <section
            class="w-full max-w-4xl bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-semibold text-gray-800 mb-6 border-b pb-2">Detail Pengambilan Sparepart</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-gray-700">
                <div>
                    <p class="font-medium">User:</p>
                    <p>{{ $pengambilanSparepart->user->name }}</p>
                </div>
                <div>
                    <p class="font-medium">Bagian:</p>
                    <p>{{ $pengambilanSparepart->bagian->nama }}</p>
                </div>
                <div>
                    <p class="font-medium">Sparepart:</p>
                    <p>{{ $pengambilanSparepart->sparepart->nama }}</p>
                </div>
                <div>
                    <p class="font-medium">Jumlah:</p>
                    <p>{{ $pengambilanSparepart->jumlah }}</p>
                </div>
                <div>
                    <p class="font-medium">Satuan:</p>
                    <p>{{ $pengambilanSparepart->satuan }}</p>
                </div>
                <div>
                    <p class="font-medium">Keperluan:</p>
                    <p>{{ $pengambilanSparepart->keperluan }}</p>
                </div>
                <div class="col-span-full">
                    <p class="font-medium">Waktu Pengambilan:</p>
                    <p>{{ $pengambilanSparepart->waktu_pengambilan }}</p>
                </div>
            </div>
            <div class="mt-6 text-right">
                <a href="{{ route('pengambilan.index') }}"
                    class="text-gray-600 font-semibold hover:text-blue-600 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </section>
    </main>
@endsection
