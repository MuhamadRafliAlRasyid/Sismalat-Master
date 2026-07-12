@extends('layouts.app')

@section('title', 'Edit Pengambilan Sparepart')

@section('content')
    <main class="p-6 flex flex-col items-center space-y-8">
        <section
            class="w-full max-w-4xl bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-semibold text-gray-800 mb-6 border-b pb-2">Edit Pengambilan Sparepart</h1>

            @if (!$pengambilanSparepart)
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-md text-center">
                    Pengambilan Sparepart tidak ditemukan.
                </div>
            @else
                <form action="{{ route('pengambilan.update', $pengambilanSparepart->hashid) }}" method="POST"
                    class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-gray-700">
                    @csrf
                    @method('PUT')

                    @if (Auth::user()->role === 'admin')
                        <!-- User (Hanya untuk admin) -->
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">User</label>
                            <select id="user_id" name="user_id" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @forelse ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('user_id', $pengambilanSparepart->user_id) == $user->hashid ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @empty
                                    <option disabled>Tidak ada user tersedia</option>
                                @endforelse
                            </select>
                            @error('user_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Bagian (Hanya untuk admin) -->
                        <div>
                            <label for="bagian_id" class="block text-sm font-medium text-gray-700 mb-1">Bagian</label>
                            <select id="bagian_id" name="bagian_id" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @forelse ($bagians as $bagian)
                                    <option value="{{ $bagian->id }}"
                                        {{ old('bagian_id', $pengambilanSparepart->bagian_id) == $bagian->hashid ? 'selected' : '' }}>
                                        {{ $bagian->nama }}
                                    </option>
                                @empty
                                    <option disabled>Tidak ada bagian tersedia</option>
                                @endforelse
                            </select>
                            @error('bagian_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <!-- Tampilkan info untuk karyawan (baca saja, tidak editable) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                            <input type="text" value="{{ $pengambilanSparepart->user->name }}" readonly
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm bg-gray-100 dark:bg-gray-800 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bagian</label>
                            <input type="text" value="{{ $pengambilanSparepart->bagian->nama ?? 'Tidak ada bagian' }}"
                                readonly
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm bg-gray-100 dark:bg-gray-800 cursor-not-allowed">
                        </div>
                    @endif

                    <!-- Sparepart -->
                    <div>
                        <label for="spareparts_id" class="block text-sm font-medium text-gray-700 mb-1">Sparepart</label>
                        <select id="spareparts_id" name="spareparts_id" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @forelse ($spareparts as $sparepart)
                                <option value="{{ $sparepart->id }}"
                                    {{ old('spareparts_id', $pengambilanSparepart->spareparts_id) == $sparepart->hashid ? 'selected' : '' }}>
                                    {{ $sparepart->nama_part }} ({{ $sparepart->kode_part }})
                                </option>
                            @empty
                                <option disabled>Tidak ada sparepart tersedia</option>
                            @endforelse
                        </select>
                        <p class="text-sm text-gray-600 mt-1">
                            Stok Baru: {{ $spareparts->first()->jumlah_baru ?? 0 }} | Stok Bekas:
                            {{ $spareparts->first()->jumlah_bekas ?? 0 }}
                        </p>
                        @error('spareparts_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="part_type" class="block text-sm font-medium text-gray-700 mb-1">Jenis Part</label>
                        <select id="part_type" name="part_type" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="baru">Part Baru</option>
                            <option value="bekas">Part Bekas</option>
                        </select>
                    </div>
                    <!-- Jumlah -->
                    <div>
                        <label for="jumlah" class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                        <input id="jumlah" name="jumlah" type="number" required
                            value="{{ old('jumlah', $pengambilanSparepart->jumlah) }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        @error('jumlah')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Satuan -->
                    <div>
                        <label for="satuan" class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                        <input id="satuan" name="satuan" type="text" required
                            value="{{ old('satuan', $pengambilanSparepart->satuan) }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        @error('satuan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Keperluan -->
                    <div class="col-span-full">
                        <label for="keperluan" class="block text-sm font-medium text-gray-700 mb-1">Keperluan</label>
                        <input id="keperluan" name="keperluan" type="text" required
                            value="{{ old('keperluan', $pengambilanSparepart->keperluan) }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        @error('keperluan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Waktu Pengambilan -->
                    <div class="col-span-full">
                        <label for="waktu_pengambilan" class="block text-sm font-medium text-gray-700 mb-1">
                            Waktu Pengambilan
                        </label>
                        <input id="waktu_pengambilan" name="waktu_pengambilan" type="datetime-local" required
                            value="{{ old('waktu_pengambilan', $pengambilanSparepart->waktu_pengambilan ? \Carbon\Carbon::parse($pengambilanSparepart->waktu_pengambilan)->format('Y-m-d\TH:i') : '') }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        @error('waktu_pengambilan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-full flex items-center gap-4 mt-6">
                        <button type="submit"
                            class="bg-blue-600 text-white font-semibold px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            <i class="fas fa-save mr-2"></i> Update
                        </button>
                        <a href="{{ route('pengambilan.index') }}"
                            class="text-gray-600 font-semibold hover:text-blue-600 transition">
                            <i class="fas fa-times mr-2"></i> Batal
                        </a>
                    </div>
                </form>
            @endif
        </section>
    </main>
@endsection
