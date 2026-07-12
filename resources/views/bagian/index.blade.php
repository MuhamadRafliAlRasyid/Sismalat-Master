@extends('layouts.app')

@section('title', 'Daftar Bagian')

@section('content')
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                <i class="fas fa-building text-amber-500"></i> Daftar Bagian
            </h2>
            <a href="{{ route('bagian.create') }}"
                class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl font-medium flex items-center gap-2 transition shadow-md shadow-amber-200">
                <i class="fas fa-plus"></i> Tambah Bagian
            </a>
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
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama bagian..."
                    class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 pl-11 bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition">
                <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
            </div>
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse ($bagians as $bagian)
                <div
                    class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-2xl shadow-md border border-amber-100 hover:shadow-xl hover:border-amber-300 transition-all duration-300 p-6 flex flex-col items-center text-center group">
                    <div
                        class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mb-4 text-amber-600 group-hover:bg-amber-200 transition">
                        <i class="fas fa-building text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 text-lg mb-1">{{ $bagian->nama }}</h3>
                    <p class="text-gray-400 text-xs mb-4">ID: {{ $bagian->hashid }}</p>

                    <div class="flex gap-2 mt-auto w-full">
                        <a href="{{ route('bagian.edit', $bagian->hashid) }}"
                            class="flex-1 bg-amber-100 text-amber-700 hover:bg-amber-200 px-3 py-2 rounded-xl text-sm font-medium transition flex items-center justify-center gap-1">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('bagian.destroy', $bagian->hashid) }}" method="POST"
                            onsubmit="return confirm('Yakin ingin menghapus bagian ini?')" class="flex-1">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="w-full bg-red-100 text-red-700 hover:bg-red-200 px-3 py-2 rounded-xl text-sm font-medium transition flex items-center justify-center gap-1">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16 text-gray-400">
                    <i class="fas fa-building text-5xl mb-4 block"></i>
                    <p class="text-lg">Tidak ada data bagian.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-10 flex justify-center">
            {{ $bagians->links() }}
        </div>
    </div>
@endsection
