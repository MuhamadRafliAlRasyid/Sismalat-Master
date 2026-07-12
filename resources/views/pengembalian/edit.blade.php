@extends('layouts.app')

@section('title', 'Edit Pengembalian')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-2xl shadow p-8">
            <h1 class="text-2xl font-bold mb-8">Edit Pengembalian Sparepart</h1>

            <form action="{{ route('pengembalian.update', $pengembalian->hashid) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Pilih Pengambilan -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2">Pilih Pengambilan</label>
                        <select name="pengambilan_id" required
                            class="w-full border rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach ($pengambilans as $p)
                                <option value="{{ $p->id }}"
                                    {{ $p->id == $pengembalian->pengambilan_id ? 'selected' : '' }}>
                                    {{ $p->sparepart->nama_part ?? '-' }}
                                    - {{ $p->user->name ?? '-' }}
                                    (Diambil: {{ $p->jumlah }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Jumlah Dikembalikan -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Jumlah Dikembalikan</label>
                        <input type="number" name="jumlah_dikembalikan"
                            value="{{ old('jumlah_dikembalikan', $pengembalian->jumlah_dikembalikan) }}" required
                            min="1" class="w-full border rounded-xl px-4 py-3">
                    </div>

                    <!-- Kondisi -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Kondisi Barang</label>
                        <select name="kondisi" required class="w-full border rounded-xl px-4 py-3">
                            <option value="baik" {{ $pengembalian->kondisi == 'baik' ? 'selected' : '' }}>Baik</option>
                            <option value="rusak" {{ $pengembalian->kondisi == 'rusak' ? 'selected' : '' }}>Rusak</option>
                        </select>
                    </div>

                    <!-- Alasan -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2">Alasan Pengembalian</label>
                        <textarea name="alasan" required rows="3" class="w-full border rounded-xl px-4 py-3">{{ old('alasan', $pengembalian->alasan) }}</textarea>
                    </div>

                    <!-- Keterangan -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2">Keterangan (Opsional)</label>
                        <textarea name="keterangan" rows="3" class="w-full border rounded-xl px-4 py-3">{{ old('keterangan', $pengembalian->keterangan) }}</textarea>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-xl transition">
                        Update Pengembalian
                    </button>
                    <a href="{{ route('pengembalian.index') }}"
                        class="px-8 py-3 border border-gray-300 rounded-xl font-medium">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
