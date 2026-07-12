@extends('layouts.app')

@section('title', 'Deleted Spareparts')

@section('content')
    <div class="flex items-center justify-center min-h-[calc(100vh-4rem)] bg-gray-100 dark:bg-gray-800 py-4">
        <div class="w-full max-w-5xl p-4">
            <h2 class="text-3xl font-bold text-red-700 mb-6 text-center border-b-2 border-red-200 pb-2">
                <i class="fas fa-trash-alt mr-2"></i> Daftar Sparepart Terhapus
            </h2>

            @if (session('success'))
                <div
                    class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-md text-center">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Tombol Kembali -->
            <div class="mb-4 text-left">
                <a href="{{ route('sparepart.index') }}"
                    class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-9000 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-md shadow-md transition duration-300 ease-in-out transform hover:-translate-y-1 hover:shadow-lg">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>

            <!-- Tabel Data Terhapus -->
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
                        @forelse ($trashedSpareparts as $sparepart)
                            <tr class="border-b hover:bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 transition duration-200">
                                <td class="py-3 px-4">{{ $sparepart->id }}</td>
                                <td class="py-3 px-4">{{ $sparepart->nama_part }}</td>
                                <td class="py-3 px-4">{{ $sparepart->model }}</td>
                                <td class="py-3 px-4">{{ $sparepart->merk }}</td>
                                <td class="py-3 px-4">{{ $sparepart->jumlah_baru }}</td>
                                <td class="py-3 px-4 flex space-x-2">
                                    <form action="{{ route('sparepart.restore', $sparepart->hashid) }}" method="POST"
                                        style="display:inline;"
                                        onsubmit="return confirm('Yakin ingin mengembalikan sparepart ini?')">

                                        @csrf

                                        <button type="submit"
                                            class="bg-green-100 text-green-600 px-3 py-1 rounded-full text-sm font-medium hover:bg-green-200 transition-all duration-200 transform hover:scale-105"
                                            title="Kembalikan Sparepart">
                                            <i class="fas fa-undo mr-1"></i> Kembalikan
                                        </button>
                                    </form>
                                    <form action="{{ route('sparepart.forceDelete', $sparepart->hashid) }}" method="POST"
                                        style="display:inline;"
                                        onsubmit="return confirm('Yakin ingin menghapus permanen? Data tidak bisa dikembalikan!')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-sm font-medium hover:bg-red-200 transition-all duration-200 transform hover:scale-105 relative group"
                                            title="Hapus Permanen">
                                            <i class="fas fa-trash mr-1"></i> Hapus Permanen
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada
                                    sparepart terhapus.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 text-center">
                {{ $trashedSpareparts->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
@endsection
