@extends('layouts.app')

@section('title', 'Daftar Pengambilan Sparepart')

@section('content')
    <div class="flex items-center justify-center min-h-[calc(100vh-4rem)] bg-gray-100 dark:bg-gray-800 py-4">
        <div class="w-full max-w-5xl p-4">
            <h2 class="text-3xl font-bold text-green-700 mb-6 text-center border-b-2 border-green-200 pb-2">
                <i class="fas fa-list mr-2"></i> Daftar Pengambilan Sparepart
            </h2>

            @if (session('success'))
                <div
                    class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-md text-center">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Form Pencarian -->
            <div class="mb-6 flex items-center space-x-2 w-full">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari user, bagian, sparepart, atau tanggal..."
                    class="w-full border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                <form action="{{ route('pengambilan.index') }}" method="GET" class="ml-2">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white rounded-full px-3 py-1 shadow-md transition duration-200">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <a href="{{ route('pengambilan.create') }}"
                class="mb-6 inline-block bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-md shadow-md transition duration-300 ease-in-out transform hover:-translate-y-1">
                <i class="fas fa-plus mr-2"></i> Tambah Pengambilan
            </a>

            <div class="overflow-x-auto bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-lg shadow-lg">
                <table class="w-full text-sm text-gray-700">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-center font-medium">No</th>
                            <th class="px-4 py-2 font-medium">User</th>
                            <th class="px-4 py-2 font-medium">Bagian</th>
                            <th class="px-4 py-2 font-medium">Sparepart</th>
                            <th class="px-4 py-2 text-center font-medium">Jumlah</th>
                            <th class="px-4 py-2 font-medium">Satuan</th>
                            <th class="px-4 py-2 font-medium">Keperluan</th>
                            <th class="px-4 py-2 font-medium">Waktu Pengambilan</th>
                            <th class="px-4 py-2 text-center font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pengambilanSpareparts as $index => $item)
                            <tr class="border-b hover:bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 transition duration-200">
                                <td class="px-4 py-2 text-center">{{ $index + 1 }}</td>
                                <td class="px-4 py-2">{{ $item->user->name ?? 'Tidak diketahui' }}</td>
                                <td class="px-4 py-2">{{ $item->bagian->nama ?? 'Tidak diketahui' }}</td>
                                <td class="px-4 py-2">{{ $item->sparepart->nama_part ?? 'Tidak diketahui' }}</td>
                                <td class="px-4 py-2 text-center text-green-700">{{ $item->jumlah }}</td>
                                <td class="px-4 py-2">{{ $item->satuan }}</td>
                                <td class="px-4 py-2">{{ $item->keperluan }}</td>
                                <td class="px-4 py-2">{{ $item->waktu_pengambilan }}</td>
                                <td class="px-4 py-2 text-center flex justify-center space-x-2">
                                    <a href="{{ route('pengambilan.edit', $item->hashid) }}"
                                        class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-sm font-medium hover:bg-blue-200 transition-all duration-200 transform hover:scale-105 relative group"
                                        title="Edit Pengambilan">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                    @if (Auth::user()->role === 'admin')
                                        <form action="{{ route('pengambilan.destroy', $item->hashid) }}" method="POST"
                                            onsubmit="return confirm('Yakin hapus data ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-sm font-medium hover:bg-red-200 transition-all duration-200 transform hover:scale-105 relative group"
                                                title="Hapus Pengambilan">
                                                <i class="fas fa-trash mr-1"></i> Hapus
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('pengambilan.exportpdf', $item->hashid) }}"
                                        class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 flex items-center text-sm font-medium transition-all duration-200 transform hover:scale-105 relative group"
                                        title="Eksport Data">
                                        <i class="fas fa-download mr-1"></i> Eksport
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada
                                    data pengambilan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 text-center">
                {{ $pengambilanSpareparts->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
@endsection
