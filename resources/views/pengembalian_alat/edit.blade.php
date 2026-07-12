@extends('layouts.app')

@section('title', 'Edit Pengembalian Alat')

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-8" x-data="pengembalianEditForm({{ json_encode([
        'jatuhTempo' => $data->pengambilan->tanggal_jatuh_tempo?->format('Y-m-d'),
        'sisaPinjaman' => $data->pengambilan->jumlah - ($data->pengambilan->pengembalians()->sum('jumlah') - $data->jumlah),
        'satuan' => $data->pengambilan->satuan ?? 'pcs',
        'jumlahSaatIni' => $data->jumlah,
    ]) }})">

        <a href="{{ route('pengembalian_alat.index') }}"
            class="inline-flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 mb-6 group transition-colors">
            <span
                class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 group-hover:bg-gray-200 flex items-center justify-center">
                <i class="fas fa-arrow-left text-sm"></i>
            </span>
            <span class="font-medium">Kembali</span>
        </a>

        <div
            class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div
                class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 p-6 sm:p-8 border-b border-amber-100 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <span
                        class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 shadow-lg shadow-amber-100">
                        <i class="fas fa-edit text-xl text-amber-500"></i>
                    </span>
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Edit Pengembalian Alat</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">
                            {{ $data->pengambilan->alat->nama_alat ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('pengembalian_alat.update', $data->hashid) }}"
                enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
                @csrf
                @method('PUT')

                {{-- Alert Status --}}
                <div x-show="isTerlambat" x-transition
                    class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded-r-xl p-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-circle text-red-500 text-xl mt-0.5"></i>
                        <div class="flex-1">
                            <h4 class="font-bold text-red-800 dark:text-red-300 text-sm">Pengembalian Melewat Tenggat</h4>
                            <p class="text-red-700 dark:text-red-400 text-xs mt-1">
                                Terlambat <span class="font-bold" x-text="hariTerlambat"></span> hari.
                                <strong>Keterangan dan foto WAJIB diisi.</strong>
                            </p>
                        </div>
                    </div>
                </div>

                <div x-show="!isTerlambat" x-transition
                    class="bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500 rounded-r-xl p-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-check-circle text-emerald-500 text-xl mt-0.5"></i>
                        <div class="flex-1">
                            <h4 class="font-bold text-emerald-800 dark:text-emerald-300 text-sm">Tepat Waktu ✓</h4>
                            <p class="text-emerald-700 dark:text-emerald-400 text-xs mt-1">
                                Keterangan dan foto bersifat opsional.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Jumlah (Auto-fill) --}}
                <div class="form-group">
                    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                        <span class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <i class="fas fa-boxes text-amber-600 dark:text-amber-400 text-xs"></i>
                        </span>
                        Jumlah Dikembalikan
                    </label>
                    <input type="hidden" name="jumlah" :value="sisaPinjaman">
                    <div
                        class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl text-gray-700 dark:text-gray-200 font-bold text-lg flex items-center justify-between">
                        <span x-text="sisaPinjaman + ' ' + satuan"></span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-normal">(otomatis)</span>
                    </div>
                </div>

                {{-- Tanggal --}}
                <div class="form-group">
                    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                        <span class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-amber-600 dark:text-amber-400 text-xs"></i>
                        </span>
                        Tanggal Pengembalian
                    </label>
                    <input type="date" name="tanggal_pengembalian"
                        value="{{ old('tanggal_pengembalian', \Carbon\Carbon::parse($data->tanggal_pengembalian)->format('Y-m-d')) }}"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200">
                </div>

                {{-- Keterangan --}}
                <div class="form-group">
                    <label class="flex items-center gap-2 text-sm font-semibold mb-2"
                        :class="isTerlambat ? 'text-red-700 dark:text-red-400' : 'text-gray-700 dark:text-gray-200'">
                        <span class="w-6 h-6 rounded-lg flex items-center justify-center"
                            :class="isTerlambat ? 'bg-red-100 dark:bg-red-900/30' : 'bg-amber-100 dark:bg-amber-900/30'">
                            <i class="fas fa-sticky-note text-xs"
                                :class="isTerlambat ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400'"></i>
                        </span>
                        Keterangan
                        <span x-show="isTerlambat" class="text-red-500">*</span>
                        <span x-show="!isTerlambat" class="text-xs text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <textarea name="keterangan" rows="4" :required="isTerlambat"
                        :class="isTerlambat ? 'border-red-300 dark:border-red-700 focus:ring-red-400' :
                            'border-gray-200 dark:border-gray-700 focus:ring-amber-400'"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:border-transparent transition-all duration-300 text-gray-700 dark:text-gray-200 resize-none"
                        :placeholder="isTerlambat ? 'Wajib jelaskan alasan keterlambatan...' : 'Catatan tambahan (opsional)'">{{ old('keterangan', $data->keterangan) }}</textarea>
                </div>

                {{-- Foto --}}
                <div class="form-group">
                    <label class="flex items-center gap-2 text-sm font-semibold mb-2"
                        :class="isTerlambat ? 'text-red-700 dark:text-red-400' : 'text-gray-700 dark:text-gray-200'">
                        <span class="w-6 h-6 rounded-lg flex items-center justify-center"
                            :class="isTerlambat ? 'bg-red-100 dark:bg-red-900/30' : 'bg-amber-100 dark:bg-amber-900/30'">
                            <i class="fas fa-camera text-xs"
                                :class="isTerlambat ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400'"></i>
                        </span>
                        Foto Bukti
                        <span x-show="isTerlambat" class="text-red-500">*</span>
                        <span x-show="!isTerlambat" class="text-xs text-gray-400 font-normal">(opsional)</span>
                    </label>

                    @if ($data->foto)
                        <div
                            class="mb-3 flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <img src="{{ $data->foto_url }}" alt="Foto bukti"
                                class="w-20 h-20 rounded-xl object-cover border">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Foto saat ini</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @{{ !isTerlambat ? 'Biarkan kosong jika tidak ingin mengubah' : 'Upload foto baru untuk mengganti' }}
                                </p>
                            </div>
                        </div>
                    @endif

                    <input type="file" name="foto" accept="image/*"
                        :required="isTerlambat && {{ $data->foto ? 'false' : 'true' }}"
                        :class="isTerlambat ? 'border-red-300 dark:border-red-700 focus:ring-red-400' :
                            'border-gray-200 dark:border-gray-700 focus:ring-amber-400'"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:border-transparent transition-all duration-300 text-gray-700 dark:text-gray-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-amber-50 dark:file:bg-amber-900/30 file:text-amber-700 dark:file:text-amber-400 hover:file:bg-amber-100">
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5 flex items-center gap-1">
                        <i class="fas fa-info-circle"></i>
                        Format JPG/PNG/WebP, maks 2MB
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="pt-6 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row gap-3 justify-end">
                    <a href="{{ route('pengembalian_alat.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3.5 border-2 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900 text-gray-700 dark:text-gray-200 font-medium rounded-2xl transition-all">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold rounded-2xl transition-all duration-300 shadow-lg shadow-amber-200 hover:shadow-xl hover:-translate-y-0.5">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function pengembalianEditForm(config) {
                return {
                    jatuhTempo: config.jatuhTempo,
                    sisaPinjaman: config.sisaPinjaman,
                    satuan: config.satuan,
                    jumlahSaatIni: config.jumlahSaatIni,

                    get isTerlambat() {
                        if (!this.jatuhTempo) return false;
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        const deadline = new Date(this.jatuhTempo);
                        deadline.setHours(0, 0, 0, 0);
                        return today > deadline;
                    },

                    get hariTerlambat() {
                        if (!this.isTerlambat) return 0;
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        const deadline = new Date(this.jatuhTempo);
                        deadline.setHours(0, 0, 0, 0);
                        const diffTime = today - deadline;
                        return Math.floor(diffTime / (1000 * 60 * 60 * 24));
                    }
                };
            }
        </script>
    @endpush
@endsection
