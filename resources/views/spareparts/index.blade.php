@extends('layouts.app')

@section('title', 'Sparepart List')

@section('content')
    <div class="flex items-center justify-center min-h-[calc(100vh-4rem)] bg-gray-100 dark:bg-gray-800 py-4">
        <div class="w-full max-w-5xl p-4">
            <h2 class="text-3xl font-bold text-indigo-700 mb-6 text-center border-b-2 border-indigo-200 pb-2">
                <i class="fas fa-list mr-2"></i> Daftar Sparepart
            </h2>

            @if (session('success'))
                <div
                    class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-md text-center">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Form Pencarian -->
            <form action="{{ route('sparepart.index') }}" method="GET" class="mb-6 flex items-center space-x-2 w-full">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama part, model, atau merk..."
                    class="w-full border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white rounded-full px-3 py-1 shadow-md transition duration-200">
                    <i class="fas fa-search"></i>
                </button>
            </form>

            <!-- Tombol Tambah (kiri) dan Unduh (kanan) -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 space-y-4 md:space-y-0">
                <!-- Tombol Tambah di Kiri -->
                <a href="{{ route('sparepart.create') }}"
                    class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-md shadow-md transition duration-300 ease-in-out transform hover:-translate-y-1">
                    <i class="fas fa-plus mr-2"></i> Tambah Sparepart
                </a>

                <!-- Tombol Unduh di Kanan -->
                <div class="flex space-x-2">
                    <a href="{{ route('sparepart.unduh') }}"
                        class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-1.5 px-3 rounded-md shadow-md text-sm transition duration-200 hover:-translate-y-0.5 flex items-center">
                        <i class="fas fa-download mr-1"></i> Unduh
                    </a>
                    <a href="{{ route('sparepart.trashed') }}"
                        class="bg-red-600 hover:bg-red-700 text-white font-medium py-1.5 px-3 rounded-md shadow-md text-sm transition duration-200 hover:-translate-y-0.5 flex items-center"
                        title="Lihat Sparepart Terhapus">
                        <i class="fas fa-trash-alt mr-1"></i> Terhapus
                    </a>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="overflow-x-auto bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-lg shadow-lg">
                <table class="w-full text-left">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="py-3 px-4 text-gray-700 font-semibold">ID</th>
                            <th class="py-3 px-4 text-gray-700 font-semibold">Nama Part</th>
                            <th class="py-3 px-4 text-gray-700 font-semibold">Model</th>
                            <th class="py-3 px-4 text-gray-700 font-semibold">Merk</th>
                            <th class="py-3 px-4 text-gray-700 font-semibold">Jumlah Baru</th>
                            <th class="py-3 px-4 text-gray-700 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($spareparts as $sparepart)
                            <tr class="border-b hover:bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 transition duration-200">
                                <td class="py-3 px-4">{{ $sparepart->id }}</td>
                                <td class="py-3 px-4">{{ $sparepart->nama_part }}</td>
                                <td class="py-3 px-4">{{ $sparepart->model }}</td>
                                <td class="py-3 px-4">{{ $sparepart->merk }}</td>
                                <td class="py-3 px-4">{{ $sparepart->jumlah_baru }}</td>
                                <td class="py-3 px-4 flex space-x-2">
                                    <a href="{{ route('sparepart.show', $sparepart->hashid) }}"
                                        class="bg-indigo-100 text-indigo-600 px-3 py-1 rounded-full text-sm font-medium hover:bg-indigo-200 transition-all duration-200 transform hover:scale-105 relative group"
                                        title="Lihat Sparepart">
                                        <i class="fas fa-eye mr-1"></i> Lihat
                                    </a>
                                    <a href="{{ route('sparepart.edit', $sparepart->hashid) }}"
                                        class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-sm font-medium hover:bg-blue-200 transition-all duration-200 transform hover:scale-105 relative group"
                                        title="Edit Sparepart">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                    <form action="{{ route('sparepart.destroy', $sparepart->hashid) }}" method="POST"
                                        style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-sm font-medium hover:bg-red-200 transition-all duration-200 transform hover:scale-105 relative group"
                                            title="Hapus Sparepart">
                                            <i class="fas fa-trash mr-1"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data
                                    sparepart.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 text-center">
                {{ $spareparts->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
@endsection
