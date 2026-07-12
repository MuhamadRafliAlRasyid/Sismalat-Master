@extends('layouts.app')

@section('title', 'Riwayat Alat: ' . $alat->nama_alat)

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8" x-data="{
        tab: 'pengambilan',
        modalOpen: false,
        modalData: null,
        modalType: ''
    }">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
                    <span class="p-2 bg-amber-100 dark:bg-amber-900/50 rounded-lg">
                        <i class="fas fa-history text-amber-600 dark:text-amber-400 text-xl"></i>
                    </span>
                    Riwayat Alat:
                    <span class="text-amber-600 dark:text-amber-400 underline decoration-amber-300 decoration-2">
                        {{ $alat->nama_alat }}
                    </span>
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 ml-11">
                    {{ $alat->merk }} &bull; {{ $alat->tipe ?? 'Tanpa Tipe' }} &bull; Stok: {{ $alat->jumlah }}
                </p>
            </div>
            <a href="{{ route('alats.show', $alat->hashid) }}"
                class="inline-flex items-center gap-2 mt-4 sm:mt-0 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 px-5 py-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm">
                <i class="fas fa-arrow-left"></i> Kembali ke Detail Alat
            </a>
        </div>

        {{-- Tabs --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="border-b border-gray-100 dark:border-gray-700">
                <nav class="flex flex-wrap -mb-px px-2">
                    <button @click="tab = 'pengambilan'"
                        :class="tab === 'pengambilan' ?
                            'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20' :
                            'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/30'"
                        class="px-5 py-4 font-medium text-sm border-b-2 transition flex items-center gap-2 rounded-t-lg">
                        <i class="fas fa-hand-holding"></i>
                        Pengambilan
                        <span
                            class="ml-1 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-800/50 text-amber-800 dark:text-amber-200">
                            {{ $pengambilan->count() }}
                        </span>
                    </button>
                    <button @click="tab = 'pengembalian'"
                        :class="tab === 'pengembalian' ?
                            'border-green-500 text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20' :
                            'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/30'"
                        class="px-5 py-4 font-medium text-sm border-b-2 transition flex items-center gap-2 rounded-t-lg">
                        <i class="fas fa-undo-alt"></i>
                        Pengembalian
                        <span
                            class="ml-1 px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 dark:bg-green-800/50 text-green-800 dark:text-green-200">
                            {{ $pengembalian->count() }}
                        </span>
                    </button>
                    <button @click="tab = 'kalibrasi'"
                        :class="tab === 'kalibrasi' ?
                            'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' :
                            'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/30'"
                        class="px-5 py-4 font-medium text-sm border-b-2 transition flex items-center gap-2 rounded-t-lg">
                        <i class="fas fa-calendar-check"></i>
                        Kalibrasi
                        <span
                            class="ml-1 px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-800/50 text-blue-800 dark:text-blue-200">
                            {{ $kalibrasis->count() }}
                        </span>
                    </button>
                </nav>
            </div>

            {{-- Tab Pengambilan --}}
            <div x-show="tab === 'pengambilan'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100" class="p-6">
                @if ($pengambilan->isEmpty())
                    <div class="text-center py-16 text-gray-500 dark:text-gray-400">
                        <i class="fas fa-inbox text-6xl mb-4 text-gray-200 dark:text-gray-600"></i>
                        <p class="text-lg">Belum ada riwayat pengambilan</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($pengambilan as $item)
                            <div class="group bg-gray-50 dark:bg-gray-700/50 rounded-xl p-5 border border-gray-100 dark:border-gray-600 hover:shadow-md hover:border-amber-300 dark:hover:border-amber-600 transition cursor-pointer"
                                @click="modalOpen = true; modalData = {{ json_encode($item) }}; modalData.foto_url = '{{ $item->foto ? asset('storage/pengambilan/' . $item->foto) : '' }}'; modalType = 'pengambilan'">
                                <div class="flex justify-between items-start mb-3">
                                    <span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ $item->status === 'dipinjam' ? 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300' : 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300' }}">
                                        {{ $item->status }}
                                    </span>
                                    @if ($item->foto)
                                        <button
                                            @click.stop="modalOpen = true; modalData = {{ json_encode($item) }}; modalData.foto_url = '{{ asset('storage/pengambilan/' . $item->foto) }}'; modalType = 'foto'"
                                            class="text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 p-1.5 rounded-lg transition">
                                            <i class="fas fa-camera"></i>
                                        </button>
                                    @endif
                                </div>
                                <h4 class="font-semibold text-gray-800 dark:text-white text-base mb-1">
                                    {{ $item->nama_peminjam ?? ($item->user->name ?? '-') }}
                                </h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                    <i class="fas fa-building mr-1"></i> {{ $item->bagian->nama ?? '-' }}
                                </p>
                                <div class="flex items-center text-sm text-gray-700 dark:text-gray-300 space-x-2 mb-3">
                                    <span
                                        class="font-bold text-amber-600 dark:text-amber-400 text-lg">{{ $item->jumlah }}</span>
                                    <span>{{ $item->satuan }}</span>
                                </div>
                                <p class="text-xs text-gray-400 dark:text-gray-500 line-clamp-2 mb-3">
                                    <i class="fas fa-clipboard-list mr-1"></i> {{ $item->keperluan }}
                                </p>
                                <div class="flex justify-between items-center text-xs text-gray-400 dark:text-gray-500">
                                    <span><i class="far fa-clock mr-1"></i>
                                        {{ \Carbon\Carbon::parse($item->waktu_pengambilan)->format('d M Y, H:i') }}</span>
                                    <span class="group-hover:text-amber-600 transition">Detail →</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Tab Pengembalian --}}
            <div x-show="tab === 'pengembalian'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100" class="p-6">
                @if ($pengembalian->isEmpty())
                    <div class="text-center py-16 text-gray-500 dark:text-gray-400">
                        <i class="fas fa-undo-alt text-6xl mb-4 text-gray-200 dark:text-gray-600"></i>
                        <p class="text-lg">Belum ada riwayat pengembalian</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($pengembalian as $item)
                            <div class="group bg-gray-50 dark:bg-gray-700/50 rounded-xl p-5 border border-gray-100 dark:border-gray-600 hover:shadow-md hover:border-green-300 dark:hover:border-green-600 transition cursor-pointer"
                                @click="modalOpen = true; modalData = {{ json_encode($item) }}; modalData.foto_url = '{{ $item->foto ? asset('storage/pengembalian/' . $item->foto) : '' }}'; modalType = 'pengembalian'">
                                <div class="flex justify-between items-start mb-3">
                                    <span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300">
                                        Dikembalikan
                                    </span>
                                    @if ($item->foto)
                                        <button
                                            @click.stop="modalOpen = true; modalData = {{ json_encode($item) }}; modalData.foto_url = '{{ asset('storage/pengembalian/' . $item->foto) }}'; modalType = 'foto'"
                                            class="text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 p-1.5 rounded-lg transition">
                                            <i class="fas fa-camera"></i>
                                        </button>
                                    @endif
                                </div>
                                <h4 class="font-semibold text-gray-800 dark:text-white text-base mb-1">
                                    {{ $item->nama_peminjam ?? ($item->user->name ?? '-') }}
                                </h4>
                                <div class="flex items-center text-sm text-gray-700 dark:text-gray-300 space-x-2 mb-2">
                                    <span
                                        class="font-bold text-green-600 dark:text-green-400 text-lg">{{ $item->jumlah }}</span>
                                    <span>unit dikembalikan</span>
                                </div>
                                @if ($item->keterangan)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-3">
                                        <i class="fas fa-sticky-note mr-1"></i> {{ $item->keterangan }}
                                    </p>
                                @endif
                                <div
                                    class="flex justify-between items-center text-xs text-gray-400 dark:text-gray-500 mt-auto">
                                    <span><i class="far fa-calendar-check mr-1"></i>
                                        {{ \Carbon\Carbon::parse($item->tanggal_pengembalian)->format('d M Y, H:i') }}</span>
                                    <span class="group-hover:text-green-600 transition">Detail →</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Tab Kalibrasi --}}
            <div x-show="tab === 'kalibrasi'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100" class="p-6">
                @if ($kalibrasis->isEmpty())
                    <div class="text-center py-16 text-gray-500 dark:text-gray-400">
                        <i class="fas fa-calendar-times text-6xl mb-4 text-gray-200 dark:text-gray-600"></i>
                        <p class="text-lg">Belum ada riwayat kalibrasi</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($kalibrasis as $item)
                            <div class="group bg-gray-50 dark:bg-gray-700/50 rounded-xl p-5 border border-gray-100 dark:border-gray-600 hover:shadow-md hover:border-blue-300 dark:hover:border-blue-600 transition cursor-pointer"
                                @click="modalOpen = true; modalData = {{ json_encode($item) }}; modalType = 'kalibrasi'">
                                <div class="flex items-start justify-between mb-3">
                                    <span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">
                                        Kalibrasi
                                    </span>
                                    <i class="fas fa-microscope text-blue-400 dark:text-blue-300"></i>
                                </div>
                                <h4 class="font-semibold text-gray-800 dark:text-white text-sm mb-2">
                                    No. Sertifikat: {{ $item->no_sertifikat ?? '-' }}
                                </h4>
                                <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400 text-xs">Tanggal</span>
                                        <p class="font-medium text-gray-700 dark:text-gray-300">
                                            {{ \Carbon\Carbon::parse($item->tanggal_kalibrasi)->format('d M Y') }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400 text-xs">Berlaku Hingga</span>
                                        <p class="font-medium text-gray-700 dark:text-gray-300">
                                            {{ \Carbon\Carbon::parse($item->masa_berlaku_baru)->format('d M Y') }}</p>
                                    </div>
                                </div>
                                @if ($item->keterangan)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                                        <i class="fas fa-info-circle mr-1"></i> {{ $item->keterangan }}
                                    </p>
                                @endif
                                <div
                                    class="text-right text-xs text-gray-400 dark:text-gray-500 mt-2 group-hover:text-blue-600 transition">
                                    Detail →
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Modal Detail (tetap sama, hanya styling diperhalus) --}}
        <div x-show="modalOpen" x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            @click.self="modalOpen = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6 relative border border-gray-100 dark:border-gray-700"
                @click.stop>
                <button @click="modalOpen = false"
                    class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition text-2xl leading-none">&times;</button>

                {{-- Pengambilan --}}
                <template x-if="modalType === 'pengambilan' && modalData">
                    <div>
                        <h3 class="text-lg font-bold text-amber-600 dark:text-amber-400 mb-4 flex items-center gap-2">
                            <i class="fas fa-hand-holding"></i> Detail Pengambilan
                        </h3>
                        <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <p><strong>Peminjam:</strong> <span
                                    x-text="modalData.nama_peminjam || modalData.user?.name || '-'"></span></p>
                            <p><strong>Bagian:</strong> <span x-text="modalData.bagian?.nama || '-'"></span></p>
                            <p><strong>Jumlah:</strong> <span
                                    x-text="modalData.jumlah + ' ' + (modalData.satuan || '')"></span></p>
                            <p><strong>Keperluan:</strong> <span x-text="modalData.keperluan || '-'"></span></p>
                            <p><strong>Waktu:</strong> <span
                                    x-text="new Date(modalData.waktu_pengambilan).toLocaleString('id-ID')"></span></p>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold mt-2 inline-block"
                                :class="modalData.status === 'dipinjam' ?
                                    'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300' :
                                    'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300'"
                                x-text="modalData.status"></span>
                        </div>
                        <template x-if="modalData.foto_url">
                            <div class="mt-4">
                                <img :src="modalData.foto_url" class="rounded-lg max-h-48 object-cover w-full"
                                    alt="Foto Pengambilan">
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Pengembalian --}}
                <template x-if="modalType === 'pengembalian' && modalData">
                    <div>
                        <h3 class="text-lg font-bold text-green-600 dark:text-green-400 mb-4 flex items-center gap-2">
                            <i class="fas fa-undo-alt"></i> Detail Pengembalian
                        </h3>
                        <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <p><strong>Pengembali:</strong> <span
                                    x-text="modalData.nama_peminjam || modalData.user?.name || '-'"></span></p>
                            <p><strong>Jumlah:</strong> <span x-text="modalData.jumlah"></span></p>
                            <p><strong>Keterangan:</strong> <span x-text="modalData.keterangan || '-'"></span></p>
                            <p><strong>Tanggal:</strong> <span
                                    x-text="new Date(modalData.tanggal_pengembalian).toLocaleString('id-ID')"></span></p>
                        </div>
                        <template x-if="modalData.foto_url">
                            <div class="mt-4">
                                <img :src="modalData.foto_url" class="rounded-lg max-h-48 object-cover w-full"
                                    alt="Foto Pengembalian">
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Foto (dari tombol kamera) --}}
                <template x-if="modalType === 'foto' && modalData">
                    <div>
                        <h3 class="text-lg font-bold text-blue-600 dark:text-blue-400 mb-4 flex items-center gap-2">
                            <i class="fas fa-camera"></i> Foto Dokumentasi
                        </h3>
                        <img :src="modalData.foto_url" class="rounded-xl max-h-96 object-contain w-full" alt="Foto">
                    </div>
                </template>

                {{-- Kalibrasi --}}
                <template x-if="modalType === 'kalibrasi' && modalData">
                    <div>
                        <h3 class="text-lg font-bold text-blue-600 dark:text-blue-400 mb-4 flex items-center gap-2">
                            <i class="fas fa-calendar-check"></i> Detail Kalibrasi
                        </h3>
                        <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <p><strong>Tanggal Kalibrasi:</strong> <span
                                    x-text="new Date(modalData.tanggal_kalibrasi).toLocaleDateString('id-ID')"></span></p>
                            <p><strong>Masa Berlaku:</strong> <span
                                    x-text="new Date(modalData.masa_berlaku_baru).toLocaleDateString('id-ID')"></span></p>
                            <p><strong>No. Sertifikat:</strong> <span x-text="modalData.no_sertifikat || '-'"></span></p>
                            <p><strong>Keterangan:</strong> <span x-text="modalData.keterangan || '-'"></span></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endpush121
