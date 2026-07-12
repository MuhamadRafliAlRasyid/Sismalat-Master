@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div
            class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-3xl shadow-xl border border-amber-100 p-8">
            <div class="flex items-center gap-3 mb-8">
                <i class="fas fa-plus-circle text-3xl text-amber-600"></i>
                <h2 class="text-2xl font-bold text-gray-800">Tambah Kategori Baru</h2>
            </div>

            <form method="POST" action="{{ route('kategoris.store') }}">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Kategori <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="nama" value="{{ old('nama') }}" required
                            class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition"
                            placeholder="Masukkan nama kategori">
                        @error('nama')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Keterangan</label>
                        <textarea name="keterangan" rows="4"
                            class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition"
                            placeholder="Deskripsi atau catatan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-10 flex gap-4 justify-end">
                    <a href="{{ route('kategoris.index') }}"
                        class="px-6 py-3 border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 hover:bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 font-medium rounded-xl transition flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                    <button type="submit"
                        class="px-8 py-3 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl transition shadow-md shadow-amber-200 flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
