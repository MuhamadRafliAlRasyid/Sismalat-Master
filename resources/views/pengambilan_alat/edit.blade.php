@extends('layouts.app')

@section('title', 'Ambil Alat')

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-8">
        {{-- Back Button --}}
        <a href="{{ route('pengambilan_alat.index') }}"
            class="inline-flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 mb-6 group transition-colors">
            <span
                class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 group-hover:bg-gray-200 flex items-center justify-center transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </span>
            <span class="font-medium">Kembali</span>
        </a>

        <div
            class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            {{-- Header --}}
            <div
                class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 p-6 sm:p-8 border-b border-amber-100 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <span
                        class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 shadow-lg shadow-amber-100">
                        <i class="fas fa-hand-holding text-xl text-amber-500"></i>
                    </span>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Ambil Alat Baru</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">Form peminjaman alat</p>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('pengambilan_alat.store') }}" enctype="multipart/form-data"
                class="p-6 sm:p-8">
                @csrf

                <div class="space-y-6">
                    {{-- Nama Peminjam (Admin Only) --}}
                    @if (auth()->user()->role == 'admin')
                        <div class="form-group">
                            <label
                                class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                                <span
                                    class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                    <i class="fas fa-user text-amber-600 dark:text-amber-400 text-xs"></i>
                                </span>
                                Nama Peminjam
                            </label>
                            <input type="text" name="nama_peminjam" value="{{ old('nama_peminjam') }}"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200"
                                placeholder="Biarkan kosong jika sama dengan user">
                        </div>
                    @endif

                    {{-- ✨ PILIH ALAT - FOLDER BASED SELECTOR ✨ --}}
                    <div class="form-group" x-data="alatSelector({{ json_encode($alatsGrouped) }}, '{{ $selectedHashid ?? '' }}')">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                            <span
                                class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <i class="fas fa-tools text-amber-600 dark:text-amber-400 text-xs"></i>
                            </span>
                            Pilih Alat <span class="text-red-500">*</span>
                        </label>

                        {{-- Hidden input untuk submit form --}}
                        <input type="hidden" name="alat_id" :value="selectedHashid">

                        {{-- Search Input --}}
                        <div class="relative mb-3">
                            <input type="text" x-model.debounce.300ms="search"
                                placeholder="Cari nama alat, merk, tipe, no seri, kelas..."
                                class="w-full pl-10 pr-10 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200 text-sm">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <button type="button" x-show="search" @click="search = ''"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </div>

                        {{-- Info bar --}}
                        <div class="flex items-center justify-between mb-2 px-1">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                <span x-text="folders.reduce((sum, f) => sum + f.count, 0)"></span> alat tersedia dalam
                                <span x-text="folders.length"></span> kategori
                            </span>
                            <span x-show="search" class="text-xs text-amber-600 dark:text-amber-400 font-medium">
                                <span x-text="filteredFolders.reduce((sum, f) => sum + f.items.length, 0)"></span> ditemukan
                            </span>
                        </div>

                        {{-- Folder List --}}
                        <div
                            class="border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden bg-white dark:bg-gray-900">
                            <div class="max-h-80 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="folder in filteredFolders" :key="folder.nama">
                                    <div>
                                        {{-- Folder Header --}}
                                        <div @click="toggleFolder(folder.nama)"
                                            class="flex items-center gap-3 p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors select-none">
                                            <i class="fas text-amber-500 text-lg"
                                                :class="isOpen(folder.nama) || search ? 'fa-folder-open' : 'fa-folder'"></i>
                                            <span class="font-semibold text-gray-800 dark:text-gray-200 flex-1 text-sm"
                                                x-text="folder.display_nama"></span>
                                            <span
                                                class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full"
                                                x-text="folder.items.length + ' varian'"></span>
                                            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200"
                                                :class="(isOpen(folder.nama) || search) ? 'rotate-180' : ''"></i>
                                        </div>

                                        {{-- Folder Items --}}
                                        <div x-show="isOpen(folder.nama) || search" x-transition
                                            class="bg-gray-50/50 dark:bg-gray-800/30 pb-1">
                                            <template x-for="item in folder.items" :key="item.hashid">
                                                <div @click="selectItem(item)"
                                                    class="flex items-center gap-3 px-4 py-2.5 cursor-pointer transition-all border-l-2 mx-2 my-0.5 rounded-r-lg"
                                                    :class="selectedHashid === item.hashid ?
                                                        'bg-amber-50 dark:bg-amber-900/20 border-amber-500 shadow-sm' :
                                                        'border-transparent hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:border-gray-300'">

                                                    {{-- Radio indicator --}}
                                                    <span
                                                        class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors"
                                                        :class="selectedHashid === item.hashid ?
                                                            'border-amber-500 bg-amber-500' :
                                                            'border-gray-300 dark:border-gray-600'">
                                                        <span x-show="selectedHashid === item.hashid"
                                                            class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                                    </span>

                                                    {{-- Item details --}}
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            <span
                                                                class="text-sm font-medium text-gray-800 dark:text-gray-200"
                                                                x-text="item.merk"></span>
                                                            <span class="text-xs text-gray-400" x-show="item.tipe !== '-'"
                                                                x-text="'• ' + item.tipe"></span>
                                                            <span class="text-xs text-gray-400"
                                                                x-text="'Seri: ' + item.no_seri"></span>
                                                        </div>
                                                        <div class="flex items-center gap-3 mt-0.5 text-xs flex-wrap">
                                                            <span class="text-gray-500 dark:text-gray-400">
                                                                Kelas: <span
                                                                    class="font-semibold text-gray-700 dark:text-gray-300"
                                                                    x-text="item.kelas"></span>
                                                            </span>
                                                            <span class="text-gray-500 dark:text-gray-400">
                                                                Kapasitas: <span
                                                                    class="font-semibold text-amber-600 dark:text-amber-400"
                                                                    x-text="item.kapasitas"></span>
                                                            </span>
                                                            <span class="text-gray-500 dark:text-gray-400">
                                                                Stok: <span class="font-semibold"
                                                                    :class="item.jumlah > 0 ?
                                                                        'text-emerald-600 dark:text-emerald-400' :
                                                                        'text-red-500'"
                                                                    x-text="item.jumlah"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Empty state --}}
                                <div x-show="filteredFolders.length === 0" class="p-8 text-center text-gray-400">
                                    <i class="fas fa-search text-3xl mb-2 block"></i>
                                    <p class="text-sm">Tidak ada alat yang ditemukan</p>
                                </div>
                            </div>
                        </div>

                        {{-- Preview Selected Item --}}
                        <div x-show="selectedItem" x-transition
                            class="mt-3 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-2xl p-4 border border-amber-200 dark:border-amber-700">
                            <div class="flex items-start gap-3">
                                <span
                                    class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center shadow-sm flex-shrink-0">
                                    <i class="fas fa-check-circle text-amber-500"></i>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-800 dark:text-gray-100"
                                        x-text="selectedItem.nama_alat"></p>
                                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 mt-2 text-sm">
                                        <div><span class="text-gray-400">Merk:</span> <span
                                                class="text-gray-700 dark:text-gray-300"
                                                x-text="selectedItem.merk"></span></div>
                                        <div><span class="text-gray-400">Tipe:</span> <span
                                                class="text-gray-700 dark:text-gray-300"
                                                x-text="selectedItem.tipe"></span></div>
                                        <div><span class="text-gray-400">No Seri:</span> <span
                                                class="text-gray-700 dark:text-gray-300"
                                                x-text="selectedItem.no_seri"></span></div>
                                        <div><span class="text-gray-400">Kelas:</span> <span
                                                class="text-gray-700 dark:text-gray-300"
                                                x-text="selectedItem.kelas"></span></div>
                                        <div><span class="text-gray-400">Kapasitas:</span> <span
                                                class="text-amber-600 dark:text-amber-400 font-semibold"
                                                x-text="selectedItem.kapasitas"></span></div>
                                        <div><span class="text-gray-400">Stok:</span> <span
                                                class="text-gray-700 dark:text-gray-300"
                                                x-text="selectedItem.jumlah"></span></div>
                                    </div>
                                </div>
                                <button type="button" @click="clearSelection()"
                                    class="text-gray-400 hover:text-red-500 transition p-1">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Validation hint --}}
                        <p x-show="!selectedHashid"
                            class="text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1 mt-2">
                            <i class="fas fa-info-circle"></i> Pilih salah satu alat untuk melanjutkan
                        </p>
                    </div>

                    {{-- Bagian --}}
                    <div class="form-group">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                            <span
                                class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <i class="fas fa-building text-amber-600 dark:text-amber-400 text-xs"></i>
                            </span>
                            Bagian <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="bagian_id" required
                                class="w-full appearance-none px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200 pr-10">
                                @foreach ($bagians as $b)
                                    <option value="{{ $b->id }}">{{ $b->nama }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Jumlah & Satuan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label
                                class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                                <span
                                    class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                    <i class="fas fa-boxes text-amber-600 dark:text-amber-400 text-xs"></i>
                                </span>
                                Jumlah <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="jumlah" value="{{ old('jumlah', 1) }}" min="1" required
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200">
                        </div>
                        <div class="form-group">
                            <label
                                class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                                <span
                                    class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                    <i class="fas fa-weight-hanging text-amber-600 dark:text-amber-400 text-xs"></i>
                                </span>
                                Satuan
                            </label>
                            <input type="text" name="satuan" value="{{ old('satuan', 'pcs') }}"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200">
                        </div>
                    </div>

                    {{-- Lama Pinjam --}}
                    <div class="form-group">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                            <span
                                class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <i class="fas fa-hourglass-half text-amber-600 dark:text-amber-400 text-xs"></i>
                            </span>
                            Lama Pinjam (Hari) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="lama_pinjam" value="{{ old('lama_pinjam', 7) }}" min="1"
                            required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200"
                            placeholder="Masukkan lama peminjaman dalam hari">
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5 flex items-center gap-1">
                            <i class="fas fa-info-circle"></i> Contoh: 7 hari, 14 hari, 30 hari
                        </p>
                    </div>

                    {{-- Keperluan --}}
                    <div class="form-group">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                            <span
                                class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <i class="fas fa-clipboard-list text-amber-600 dark:text-amber-400 text-xs"></i>
                            </span>
                            Keperluan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="keperluan" rows="4" required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200 resize-none"
                            placeholder="Jelaskan keperluan penggunaan alat...">{{ old('keperluan') }}</textarea>
                    </div>

                    {{-- Waktu --}}
                    <div class="form-group">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                            <span
                                class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-amber-600 dark:text-amber-400 text-xs"></i>
                            </span>
                            Waktu Pengambilan
                        </label>
                        <input type="datetime-local" name="waktu_pengambilan"
                            value="{{ old('waktu_pengambilan', now()->format('Y-m-d\TH:i')) }}"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200">
                    </div>

                    {{-- Foto --}}
                    <div class="form-group">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                            <span
                                class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <i class="fas fa-camera text-amber-600 dark:text-amber-400 text-xs"></i>
                            </span>
                            Foto Bukti (opsional)
                        </label>
                        <input type="file" name="foto" accept="image/*"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-amber-50 dark:file:bg-amber-900/30 file:text-amber-700 dark:file:text-amber-400 hover:file:bg-amber-100">
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5 flex items-center gap-1">
                            <i class="fas fa-info-circle"></i> Format JPG/PNG/WebP, maks 2MB
                        </p>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div
                    class="mt-8 flex flex-col sm:flex-row gap-3 justify-end border-t border-gray-100 dark:border-gray-700 pt-8">
                    <a href="{{ route('pengambilan_alat.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3.5 border-2 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900 text-gray-700 dark:text-gray-200 font-medium rounded-2xl transition-all duration-300">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold rounded-2xl transition-all duration-300 shadow-lg shadow-amber-200 hover:shadow-xl hover:-translate-y-0.5">
                        <i class="fas fa-save"></i> Simpan Pengambilan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function alatSelector(folders, preselectedHashid) {
                return {
                    folders: folders,
                    search: '',
                    openFolders: [],
                    selectedHashid: preselectedHashid || '',

                    init() {
                        // Auto-open folder yang berisi alat yang sudah dipilih (untuk edit mode)
                        if (this.selectedHashid) {
                            const folder = this.folders.find(f =>
                                f.items.some(item => item.hashid === this.selectedHashid)
                            );
                            if (folder) {
                                this.openFolders.push(folder.nama);
                            }
                        }
                    },

                    get filteredFolders() {
                        if (!this.search) return this.folders;
                        const q = this.search.toLowerCase();
                        return this.folders
                            .map(folder => {
                                const matchFolder = folder.display_nama.toLowerCase().includes(q);
                                const filteredItems = folder.items.filter(item => {
                                    return [item.nama_alat, item.merk, item.tipe, item.no_seri, item.kapasitas,
                                            item.kelas
                                        ]
                                        .some(val => val && val.toString().toLowerCase().includes(q));
                                });

                                if (matchFolder) return folder;
                                if (filteredItems.length > 0) return {
                                    ...folder,
                                    items: filteredItems
                                };
                                return null;
                            })
                            .filter(f => f !== null);
                    },

                    get selectedItem() {
                        if (!this.selectedHashid) return null;
                        for (const folder of this.folders) {
                            const item = folder.items.find(i => i.hashid === this.selectedHashid);
                            if (item) return item;
                        }
                        return null;
                    },

                    toggleFolder(name) {
                        const idx = this.openFolders.indexOf(name);
                        if (idx >= 0) {
                            this.openFolders.splice(idx, 1);
                        } else {
                            this.openFolders.push(name);
                        }
                    },

                    isOpen(name) {
                        return this.openFolders.includes(name);
                    },

                    selectItem(item) {
                        this.selectedHashid = item.hashid;
                    },

                    clearSelection() {
                        this.selectedHashid = '';
                    }
                };
            }
        </script>
    @endpush
@endsection
