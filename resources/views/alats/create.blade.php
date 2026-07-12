@extends('layouts.app')

@section('title', 'Tambah Alat Baru')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div
            class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-3xl shadow-xl border border-amber-100 p-8">
            <div class="flex items-center gap-3 mb-8">
                <i class="fas fa-plus-circle text-3xl text-amber-600"></i>
                <h2 class="text-2xl font-bold text-gray-800">Tambah Alat Baru</h2>
            </div>

            <form method="POST" action="{{ route('alats.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Alat <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="nama_alat" value="{{ old('nama_alat') }}"
                            class="w-full border @error('nama_alat') border-red-300 @else border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition"
                            placeholder="Contoh: Multimeter Digital" required>
                        @error('nama_alat')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Kelas</label>
                        <input type="text" name="kelas" value="{{ old('kelas') }}"
                            class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition"
                            placeholder="Contoh: A, B, 0.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Merk <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="merk" value="{{ old('merk') }}"
                            class="w-full border @error('merk') border-red-300 @else border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition"
                            placeholder="Contoh: Fluke, Bosch" required>
                        @error('merk')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipe</label>
                        <input type="text" name="tipe" value="{{ old('tipe') }}"
                            class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition"
                            placeholder="Contoh: FLUKE 87V">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nomor Seri</label>
                        <input type="text" name="no_seri" value="{{ old('no_seri') }}"
                            class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition"
                            placeholder="Masukkan nomor seri">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nomor Identitas</label>
                        <input type="text" name="no_identitas" value="{{ old('no_identitas') }}"
                            class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition"
                            placeholder="ID internal/aset">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Kapasitas</label>
                        <input type="text" name="kapasitas" value="{{ old('kapasitas') }}"
                            class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition"
                            placeholder="Contoh: 1000V, 10A">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Daya Baca</label>
                        <input type="text" name="daya_baca" value="{{ old('daya_baca') }}"
                            class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition"
                            placeholder="Resolusi / ketelitian">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Jumlah <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="jumlah" value="{{ old('jumlah', 1) }}" min="1"
                            class="w-full border @error('jumlah') border-red-300 @else border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition"
                            required>
                        @error('jumlah')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">No Sertifikat</label>
                        <input type="text" name="no_sertifikat" value="{{ old('no_sertifikat') }}"
                            class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition"
                            placeholder="Nomor sertifikat kalibrasi">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Kategori</label>
                        <select name="kategori_id"
                            class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($kategoris ?? [] as $kategori)
                                <option value="{{ $kategori->id }}"
                                    {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>{{ $kategori->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Masa Berlaku</label>
                        <input type="date" name="masa_berlaku" value="{{ old('masa_berlaku') }}"
                            class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Foto Alat</label>
                        <input type="file" name="foto" accept="image/*"
                            class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                        <p class="text-xs text-gray-400 mt-1">Format: JPG/PNG/WebP. Maks 2MB. Foto akan dikompres otomatis.
                        </p>
                        @error('foto')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div
                    class="mt-6 bg-amber-50 p-4 rounded-xl border border-amber-100 text-sm text-amber-700 flex items-center gap-2">
                    <i class="fas fa-qrcode"></i> QR Code akan digenerate otomatis setelah data disimpan.
                </div>

                <div class="mt-10 flex gap-4 justify-end">
                    <a href="{{ route('alats.index') }}"
                        class="px-6 py-3 border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 hover:bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 font-medium rounded-xl transition flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                    <button type="submit"
                        class="px-8 py-3 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl transition shadow-md shadow-amber-200 hover:shadow-lg flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Alat
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
