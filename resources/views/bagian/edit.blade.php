@extends('layouts.app')

@section('title', 'Edit Bagian')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div
            class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-3xl shadow-xl border border-amber-100 p-8">
            <div class="flex items-center gap-3 mb-8">
                <i class="fas fa-edit text-3xl text-amber-600"></i>
                <h2 class="text-2xl font-bold text-gray-800">Edit Bagian</h2>
            </div>

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('bagian.update', $bagian->hashid) }}">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Bagian <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama', $bagian->nama) }}" required
                        class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition">
                </div>

                <div class="mt-10 flex gap-4 justify-end">
                    <a href="{{ route('bagian.index') }}"
                        class="px-6 py-3 border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 hover:bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 font-medium rounded-xl transition flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                    <button type="submit"
                        class="px-8 py-3 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl transition shadow-md shadow-amber-200 flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
