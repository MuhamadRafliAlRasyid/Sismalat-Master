@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    {{-- Panggil komponen onboarding di awal konten --}}
    @include('components.onboarding')

    <div class="space-y-6">
        {{-- Header Info --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-amber-100 p-6 flex flex-col sm:flex-row items-center gap-4">
            <img src="{{ asset('images/profile/' . Auth::user()->profile_photo_path) }}"
                class="w-16 h-16 rounded-full border-2 border-amber-300"
                onerror="this.src='{{ asset('images/avatar.png') }}'">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Halo, {{ Auth::user()->name }} 🎉</h2>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Semoga harimu menyenangkan! Berikut ringkasan
                    aktivitasmu.</p>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div id="stat-cards" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-amber-100 p-5 flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center text-2xl">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Dipinjam</div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-white">{{ $totalDipinjam }}</div>
                </div>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-amber-100 p-5 flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-2xl">
                    <i class="fas fa-undo-alt"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Dikembalikan</div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-white">{{ $totalDikembalikan }}</div>
                </div>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-amber-100 p-5 flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-2xl">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Scan QR</div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">Ambil / Kembalikan</div>
                </div>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-amber-100 p-5 flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center text-2xl">
                    <i class="fas fa-tools"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Alat Tersedia</div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-white">{{ \App\Models\Alat::count() }}</div>
                </div>
            </div>
        </div>

        {{-- Dua Kolom Utama --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Alat Sedang Dipinjam --}}
            <div id="alat-dipinjam-section"
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-amber-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center"><i
                            class="fas fa-clock"></i></span>
                    Alat Sedang Dipinjam
                </h3>
                @if ($alatDipinjam->count() > 0)
                    <div class="space-y-3">
                        @foreach ($alatDipinjam as $item)
                            <div class="flex items-center gap-3 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl">
                                @if ($item->alat && $item->alat->foto)
                                    <img src="{{ $item->alat->foto_thumb }}" class="w-12 h-12 rounded-lg object-cover">
                                @else
                                    <div
                                        class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-300">
                                        <i class="fas fa-tools text-xl"></i>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-800 dark:text-white truncate">
                                        {{ $item->alat->nama_alat ?? '-' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->jumlah }}
                                        {{ $item->satuan }} •
                                        {{ \Carbon\Carbon::parse($item->waktu_pengambilan)->format('d M H:i') }}</p>
                                </div>
                                <a href="{{ route('pengembalian_alat.create', $item->hashid) }}"
                                    class="px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-medium hover:bg-emerald-200 transition dark:bg-emerald-900 dark:text-emerald-300">
                                    Kembalikan
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-gray-400">
                        <i class="fas fa-check-circle text-3xl mb-2 text-emerald-400"></i>
                        <p>Tidak ada alat dipinjam</p>
                    </div>
                @endif
            </div>

            {{-- Aktivitas Terbaru --}}
            <div id="aktivitas-terbaru-section"
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-amber-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center"><i
                            class="fas fa-history"></i></span>
                    Aktivitas Terbaru
                </h3>
                @if ($pengambilanTerbaru->count() > 0)
                    <div class="space-y-3">
                        @foreach ($pengambilanTerbaru as $ambil)
                            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-xl">
                                <div
                                    class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm
                                    {{ $ambil->status == 'dipinjam' ? 'bg-red-400' : 'bg-emerald-400' }}">
                                    <i class="fas {{ $ambil->status == 'dipinjam' ? 'fa-arrow-up' : 'fa-check' }}"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate">
                                        {{ $ambil->alat->nama_alat ?? '-' }}</p>
                                    <p class="text-xs text-gray-400">{{ $ambil->created_at->diffForHumans() }}</p>
                                </div>
                                <span
                                    class="text-xs px-2 py-1 rounded-full
                                    {{ $ambil->status == 'dipinjam' ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300' }}">
                                    {{ $ambil->status == 'dipinjam' ? 'Dipinjam' : 'Kembali' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-gray-400">Belum ada aktivitas</div>
                @endif
            </div>
        </div>
    </div>
@endsection
