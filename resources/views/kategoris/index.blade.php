@extends('layouts.app')

@section('title', 'Daftar Kategori')

@section('content')
    <div class="max-w-7xl mx-auto px-4">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <i class="fas fa-tags text-amber-500"></i> Daftar Kategori
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Kelola kategori alat dan sparepart</p>
            </div>
            <a href="{{ route('kategoris.create') }}"
                class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl font-medium flex items-center gap-2 transition shadow-md shadow-amber-200">
                <i class="fas fa-plus"></i> Tambah Kategori
            </a>
        </div>

        {{-- Search --}}
        <form method="GET" class="mb-8">
            <div class="relative max-w-md">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama kategori..."
                    class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 pl-11 bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition">
                <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
            </div>
        </form>

        {{-- Grid Kartu Kategori --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($data as $kategori)
                <div
                    class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-2xl shadow-md border border-amber-100 hover:shadow-xl hover:border-amber-300 transition-all duration-300 p-6 flex flex-col group">
                    {{-- Ikon Kategori --}}
                    <div
                        class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center mb-4 text-amber-600 group-hover:bg-amber-200 transition">
                        <i class="fas fa-tag text-xl"></i>
                    </div>

                    {{-- Nama Kategori --}}
                    <h3 class="font-bold text-gray-800 text-lg mb-1">{{ $kategori->nama }}</h3>

                    {{-- Keterangan --}}
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-4 flex-1">
                        {{ $kategori->keterangan ? Str::limit($kategori->keterangan, 60) : 'Tidak ada keterangan' }}
                    </p>

                    {{-- Jumlah Alat & Sparepart dalam Kategori (opsional) --}}
                    <div class="flex items-center gap-4 text-xs text-gray-400 mb-4">
                        @if (method_exists($kategori, 'alats'))
                            <span class="flex items-center gap-1">
                                <i class="fas fa-tools"></i> {{ $kategori->alats->count() }} Alat
                            </span>
                        @endif
                        @if (method_exists($kategori, 'spareparts'))
                            <span class="flex items-center gap-1">
                                <i class="fas fa-boxes"></i> {{ $kategori->spareparts->count() }} Sparepart
                            </span>
                        @endif
                    </div>

                    {{-- Aksi (ikon saja agar tidak keluar card) --}}
                    <div class="flex justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('kategoris.show', $kategori->hashid) }}"
                            class="p-2 bg-amber-100 text-amber-700 rounded-xl hover:bg-amber-200 transition" title="Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('kategoris.edit', $kategori->hashid) }}"
                            class="p-2 bg-blue-100 text-blue-700 rounded-xl hover:bg-blue-200 transition" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('kategoris.destroy', $kategori->hashid) }}"
                            onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="p-2 bg-red-100 text-red-700 rounded-xl hover:bg-red-200 transition" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-20 text-gray-400">
                    <i class="fas fa-tags text-6xl mb-4 block"></i>
                    <p class="text-lg font-medium">Belum ada kategori</p>
                    <p class="text-sm mt-1">Klik "Tambah Kategori" untuk memulai</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-10 flex justify-center">
            {{ $data->links() }}
        </div>
    </div>
@endsection
