@extends('layouts.app')

@section('title', 'Detail Purchase Request')

@section('content')
    <main class="p-6 flex flex-col items-center space-y-8 bg-gradient-to-br from-gray-100 to-white min-h-[calc(100vh-4rem)]">
        <section
            class="w-full max-w-4xl bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-xl shadow-2xl p-6 transform transition-all duration-300 hover:shadow-3xl">
            <h1 class="text-3xl font-bold text-indigo-800 mb-6 border-b-2 border-indigo-200 pb-3 flex items-center">
                <i class="fas fa-info-circle mr-2 text-indigo-600"></i> Detail Purchase Request
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-700">
                <!-- Nama Part -->
                <div
                    class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 p-4 rounded-lg shadow-inner hover:shadow-md transition-shadow">
                    <p class="font-semibold text-indigo-600 flex items-center" title="Nama dari sparepart">
                        <i class="fas fa-tag mr-2"></i> Nama Part:
                    </p>
                    <p class="mt-1 text-lg font-medium break-words">{{ $purchaseRequest->nama_part }}</p>
                </div>

                <!-- Part Number -->
                <div
                    class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 p-4 rounded-lg shadow-inner hover:shadow-md transition-shadow">
                    <p class="font-semibold text-indigo-600 flex items-center" title="Nomor identifikasi sparepart">
                        <i class="fas fa-barcode mr-2"></i> Part Number:
                    </p>
                    <p class="mt-1 text-lg font-medium">{{ $purchaseRequest->part_number }}</p>
                </div>

                <!-- Link Website -->
                <div
                    class="col-span-full bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 p-4 rounded-lg shadow-inner hover:shadow-md transition-shadow">
                    <p class="font-semibold text-indigo-600 flex items-center" title="Tautan ke situs web terkait">
                        <i class="fas fa-link mr-2"></i> Link Website:
                    </p>
                    <p class="mt-1 text-lg font-medium break-words">
                        @if ($purchaseRequest->link_website)
                            <a href="{{ $purchaseRequest->link_website }}" target="_blank"
                                class="text-blue-600 hover:underline">
                                {{ $purchaseRequest->link_website }}
                            </a>
                        @else
                            Tidak ada
                        @endif
                    </p>
                </div>

                <!-- Waktu Request -->
                <div
                    class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 p-4 rounded-lg shadow-inner hover:shadow-md transition-shadow">
                    <p class="font-semibold text-indigo-600 flex items-center" title="Waktu pengajuan permintaan">
                        <i class="fas fa-clock mr-2"></i> Tanggal Request:
                    </p>
                    <p class="mt-1 text-lg font-medium">{{ $purchaseRequest->waktu_request }}</p>
                </div>

                <!-- Quantity -->
                <div
                    class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 p-4 rounded-lg shadow-inner hover:shadow-md transition-shadow">
                    <p class="font-semibold text-indigo-600 flex items-center" title="Jumlah permintaan">
                        <i class="fas fa-box-open mr-2"></i> Quantity:
                    </p>
                    <p class="mt-1 text-lg font-medium">{{ $purchaseRequest->quantity }}</p>
                </div>

                <!-- Satuan -->
                <div
                    class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 p-4 rounded-lg shadow-inner hover:shadow-md transition-shadow">
                    <p class="font-semibold text-indigo-600 flex items-center" title="Satuan jumlah">
                        <i class="fas fa-ruler mr-2"></i> Satuan:
                    </p>
                    <p class="mt-1 text-lg font-medium">{{ $purchaseRequest->satuan }}</p>
                </div>

                <!-- Masa Delivery -->
                <div
                    class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 p-4 rounded-lg shadow-inner hover:shadow-md transition-shadow">
                    <p class="font-semibold text-indigo-600 flex items-center" title="Waktu pengiriman diperkirakan">
                        <i class="fas fa-truck mr-2"></i> Masa Delivery:
                    </p>
                    <p class="mt-1 text-lg font-medium">{{ $purchaseRequest->mas_deliver }}</p>
                </div>

                <!-- Untuk Apa -->
                <div
                    class="col-span-full bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 p-4 rounded-lg shadow-inner hover:shadow-md transition-shadow">
                    <p class="font-semibold text-indigo-600 flex items-center" title="Keterangan penggunaan">
                        <i class="fas fa-info mr-2"></i> Keterangan:
                    </p>
                    <p class="mt-1 text-lg font-medium break-words">{{ $purchaseRequest->untuk_apa }}</p>
                </div>

                <!-- PIC -->
                <div
                    class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 p-4 rounded-lg shadow-inner hover:shadow-md transition-shadow">
                    <p class="font-semibold text-indigo-600 flex items-center" title="Person in Charge">
                        <i class="fas fa-user mr-2"></i> PIC:
                    </p>
                    <p class="mt-1 text-lg font-medium">{{ $purchaseRequest->pic }}</p>
                </div>

                <!-- Quotation Lead Time -->
                <div
                    class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 p-4 rounded-lg shadow-inner hover:shadow-md transition-shadow">
                    <p class="font-semibold text-indigo-600 flex items-center" title="Waktu penyediaan penawaran">
                        <i class="fas fa-hourglass mr-2"></i> Quotation Lead Time:
                    </p>
                    <p class="mt-1 text-lg font-medium">{{ $purchaseRequest->quotation_lead_time ?? 'Tidak ada' }}</p>
                </div>

                <!-- Status -->
                <div
                    class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 p-4 rounded-lg shadow-inner hover:shadow-md transition-shadow">
                    <p class="font-semibold text-indigo-600 flex items-center" title="Status pengajuan">
                        <i class="fas fa-circle mr-2"></i> Status:
                    </p>
                    <p
                        class="mt-1 text-lg font-medium {{ $purchaseRequest->status === 'Completed' ? 'text-green-600' : 'text-yellow-600' }}">
                        {{ $purchaseRequest->status }}
                    </p>
                </div>

                <!-- User -->
                <!-- User -->
                <div
                    class="col-span-full bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 p-4 rounded-lg shadow-inner hover:shadow-md transition-shadow">
                    <p class="font-semibold text-indigo-600 flex items-center" title="Pengaju permintaan">
                        User:
                    </p>
                    <p class="mt-1 text-lg font-medium">
                        {{ $purchaseRequest->user->name ?? 'User tidak ditemukan' }}
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <a href="{{ route('purchase_requests.index') }}"
                    class="flex items-center text-gray-700 font-semibold hover:text-indigo-700 transition duration-300 transform hover:scale-105">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
                <!-- resources/views/purchase_requests/show.blade.php -->

                <div class="mt-6 flex gap-3">
                    @if ($purchaseRequest->status === 'PO' && Auth::user()->role === 'admin')
                        <form action="{{ route('purchase_requests.complete', $purchaseRequest->hashid) }}" method="POST"
                            class="inline">
                            @csrf
                            <button type="submit"
                                class="bg-green-600 text-white font-bold px-5 py-2 rounded-lg hover:bg-green-700 transition shadow-md flex items-center gap-2">
                                <i class="fas fa-check-circle"></i> Complete & Tambah Stok
                            </button>
                        </form>
                    @endif

                    @if ($purchaseRequest->status === 'PR' && Auth::user()->role === 'super')
                        <form action="{{ route('purchase_requests.approve', $purchaseRequest->hashid) }}" method="POST"
                            class="inline">
                            @csrf
                            <button type="submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                                Approve → PO
                            </button>
                        </form>
                    @endif
                </div>
                @if ($purchaseRequest->status !== 'Completed')
                    <a href="{{ route('purchase_requests.edit', $purchaseRequest->hashid) }}"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow-md hover:bg-blue-700 transition transform hover:scale-105 flex items-center group"
                        title="Ubah detail pengajuan">
                        <i class="fas fa-edit mr-2"></i> Edit
                    </a>
                @endif
                <button onclick="window.print()"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg shadow-md hover:bg-gray-700 transition transform hover:scale-105 flex items-center group"
                    title="Cetak detail pengajuan">
                    <i class="fas fa-print mr-2"></i> Cetak
                </button>
            </div>
            </div>
        </section>
    </main>

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        .truncate {
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .break-words {
            word-break: break-word;
        }
    </style>
@endsection
