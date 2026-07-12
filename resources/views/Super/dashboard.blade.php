@extends('layouts.app')

@section('title', 'Dashboard Super')

@section('content')
    <main class="p-6 flex flex-col items-center space-y-8">
        <section
            class="w-full max-w-4xl bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-semibold text-gray-800 mb-6 border-b pb-2">Dashboard Super</h1>

            <!-- Statistik atau Ringkasan -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-blue-100 p-4 rounded-lg shadow hover:shadow-md transition">
                    <h3 class="text-lg font-medium text-gray-700">Total Pengajuan</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ $totalRequests ?? 0 }}</p>
                </div>
                <div class="bg-green-100 p-4 rounded-lg shadow hover:shadow-md transition">
                    <h3 class="text-lg font-medium text-gray-700">Disetujui</h3>
                    <p class="text-2xl font-bold text-green-600">{{ $approvedRequests ?? 0 }}</p>
                </div>
                <div class="bg-red-100 p-4 rounded-lg shadow hover:shadow-md transition">
                    <h3 class="text-lg font-medium text-gray-700">Ditolak</h3>
                    <p class="text-2xl font-bold text-red-600">{{ $rejectedRequests ?? 0 }}</p>
                </div>
            </div>

            <!-- Tautan Cepat -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Tautan Cepat</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="{{ route('purchase_requests.index') }}"
                        class="bg-blue-600 text-white font-semibold px-4 py-3 rounded-md hover:bg-blue-700 transition flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> Approve Pengajuan Sparepart
                    </a>
                </div>
            </div>

            <!-- Notifikasi atau Aktivitas Terbaru -->
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Aktivitas Terbaru</h2>
                <div class="space-y-4">
                    @forelse ($recentLogs as $log)
                        <div
                            class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 p-4 rounded-lg shadow hover:shadow-md transition">
                            <p class="text-gray-700"><strong>Aksi:</strong> {{ $log->action }}</p>
                            <p class="text-gray-600"><strong>Permintaan:</strong>
                                {{ $log->purchaseRequest->nama_part ?? 'N/A' }}</p>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Oleh:
                                {{ $log->approvedBy->name ?? 'Unknown' }} pada
                                {{ $log->created_at->format('d M Y H:i') }}</p>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-center">Tidak ada aktivitas terbaru.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </main>
@endsection
