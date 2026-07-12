@extends('layouts.app')

@section('title', 'Daftar Pengembalian Sparepart')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Daftar Pengembalian Sparepart</h1>
            <a href="{{ route('pengembalian.create') }}"
                class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-xl font-medium flex items-center gap-2">
                <i class="fas fa-plus"></i> Tambah Pengembalian
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-2xl shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-4 text-left">Sparepart</th>
                        <th class="px-6 py-4 text-left">User</th>
                        <th class="px-6 py-4 text-center">Jumlah Dikembalikan</th>
                        <th class="px-6 py-4 text-center">Kondisi</th>
                        <th class="px-6 py-4 text-left">Alasan</th>
                        <th class="px-6 py-4 text-center">Tanggal</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($pengembalians as $item)
                        <tr class="hover:bg-gray-50 dark:bg-gray-900 dark:bg-gray-900">
                            <td class="px-6 py-4">{{ $item->sparepart->nama_part ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $item->user->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-center font-semibold">{{ $item->jumlah_dikembalikan }}</td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="px-4 py-1 rounded-full text-xs font-medium
                                    {{ $item->kondisi == 'baik' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ ucfirst($item->kondisi) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">{{ Str::limit($item->alasan, 60) }}</td>
                            <td class="px-6 py-4 text-center text-sm">{{ $item->tanggal_kembali?->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('pengembalian.edit', $item->hashid) }}"
                                    class="text-blue-600 hover:text-blue-800 mr-4">Edit</a>
                                <form action="{{ route('pengembalian.destroy', $item->hashid) }}" method="POST"
                                    class="inline" onsubmit="return confirm('Yakin hapus pengembalian ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-gray-500 dark:text-gray-400">
                                Belum ada data pengembalian sparepart.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $pengembalians->links() }}
        </div>
    </div>
@endsection
