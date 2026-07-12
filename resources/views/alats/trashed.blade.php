@extends('layouts.app')

@section('title', 'Data Alat Terhapus')

@section('content')
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3 mb-8">
            <i class="fas fa-trash-restore text-red-500"></i> Data Alat Terhapus
        </h2>

        @if ($data->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($data as $alat)
                    <div x-data="{ showForceDeleteModal: false }"
                        class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-2xl shadow-md border border-red-100 p-5 hover:shadow-lg transition transform hover:-translate-y-1">
                        <h3 class="font-bold text-gray-800 text-lg">{{ $alat->nama_alat }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $alat->merk }} &bullet;
                            {{ $alat->tipe }}</p>
                        <p class="text-xs text-gray-400 mt-2">Dihapus: {{ $alat->deleted_at->diffForHumans() }}</p>
                        <div class="mt-4 flex gap-2">
                            <form method="POST" action="{{ route('alats.restore', $alat->hashid) }}" class="flex-1">
                                @csrf
                                <button type="submit"
                                    class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-2.5 rounded-xl font-medium transition flex items-center justify-center gap-2 shadow-sm">
                                    <i class="fas fa-undo-alt"></i> Pulihkan
                                </button>
                            </form>
                            <button @click="showForceDeleteModal = true"
                                class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2.5 rounded-xl font-medium transition flex items-center justify-center gap-2 shadow-sm">
                                <i class="fas fa-trash"></i> Hapus
                            </button>

                            {{-- Modal Konfirmasi Hapus Permanen --}}
                            <div x-show="showForceDeleteModal"
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                                x-transition>
                                <div class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-2xl p-6 max-w-sm mx-4 shadow-2xl"
                                    @click.away="showForceDeleteModal = false">
                                    <h3 class="text-lg font-bold text-gray-800 mb-2">Hapus Permanen</h3>
                                    <p class="text-gray-600 mb-6">Data <strong>{{ $alat->nama_alat }}</strong> akan dihapus
                                        selamanya dan tidak bisa dikembalikan.</p>
                                    <div class="flex justify-end gap-3">
                                        <button @click="showForceDeleteModal = false"
                                            class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 rounded-xl transition">Batal</button>
                                        <form method="POST" action="{{ route('alats.forceDelete', $alat->hashid) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl transition">Hapus
                                                Permanen</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-16 text-gray-400">
                <i class="fas fa-box-open text-5xl mb-4 block"></i>
                Tidak ada data terhapus
            </div>
        @endif
    </div>
@endsection
