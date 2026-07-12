@extends('layouts.app')
@section('title', 'Detail Sparepart')
@section('content')
    <div class="flex items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-800 py-6">
        <div class="w-full max-w-3xl">
            <div class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 shadow-xl rounded-lg p-6">
                <h2 class="text-3xl font-bold text-indigo-700 mb-6 text-center border-b-2 border-indigo-200 pb-2 relative">
                    <i class="fas fa-info-circle mr-2"></i> Detail Sparepart
                    <a href="{{ route('sparepart.edit', $sparepart->hashid) }}"
                        class="absolute right-0 top-0 text-blue-500 hover:text-blue-700 underline text-sm mt-1 mr-2">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nama Part -->
                    <div class="bg-blue-50 p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 font-semibold flex items-center" title="Nama dari sparepart">
                            <i class="fas fa-tag mr-2"></i> Nama Part
                        </p>
                        <p class="text-lg text-gray-800 break-words">{{ $sparepart->nama_part }}</p>
                    </div>

                    <!-- Model -->
                    <div class="bg-green-50 p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 font-semibold flex items-center" title="Model sparepart">
                            <i class="fas fa-cogs mr-2"></i> Model
                        </p>
                        <p class="text-lg text-gray-800">{{ $sparepart->model }}</p>
                    </div>

                    <!-- Merk -->
                    <div class="bg-yellow-50 p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 font-semibold flex items-center" title="Merek sparepart">
                            <i class="fas fa-trademark mr-2"></i> Merk
                        </p>
                        <p class="text-lg text-gray-800">{{ $sparepart->merk }}</p>
                    </div>

                    <!-- Jumlah Baru -->
                    <div class="bg-red-50 p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 font-semibold flex items-center" title="Jumlah sparepart baru">
                            <i class="fas fa-box-open mr-2"></i> Jumlah Baru
                        </p>
                        <p class="text-lg text-gray-800">{{ $sparepart->jumlah_baru }}</p>
                    </div>

                    <!-- Jumlah Bekas -->
                    <div class="bg-purple-50 p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 font-semibold flex items-center" title="Jumlah sparepart bekas">
                            <i class="fas fa-box mr-2"></i> Jumlah Bekas
                        </p>
                        <p class="text-lg text-gray-800">{{ $sparepart->jumlah_bekas }}</p>
                    </div>

                    <!-- Supplier -->
                    <div class="bg-indigo-50 p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 font-semibold flex items-center" title="Pemasok sparepart">
                            <i class="fas fa-truck mr-2"></i> Supplier
                        </p>
                        <p class="text-lg text-gray-800 break-words">{{ $sparepart->supplier }}</p>
                    </div>

                    <!-- Patokan Harga -->
                    <div class="bg-teal-50 p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 font-semibold flex items-center" title="Harga referensi">
                            <i class="fas fa-dollar-sign mr-2"></i> Patokan Harga
                        </p>
                        <p class="text-lg text-gray-800">
                            Rp {{ number_format($sparepart->patokan_harga ?? 0, 2, ',', '.') }}
                        </p>
                    </div>

                    <!-- Total -->
                    <div class="bg-orange-50 p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 font-semibold flex items-center" title="Total harga">
                            <i class="fas fa-money-bill-wave mr-2"></i> Total
                        </p>
                        <p class="text-lg text-gray-800">
                            Rp {{ number_format($sparepart->total ?? 0, 2, ',', '.') }}
                        </p>
                    </div>

                    <!-- RUK No -->
                    <div class="bg-pink-50 p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 font-semibold flex items-center" title="Nomor RUK (lokasi penyimpanan)">
                            <i class="fas fa-warehouse mr-2"></i> RUK No
                        </p>
                        <p class="text-lg text-gray-800">{{ $sparepart->ruk_no }}</p>
                    </div>

                    <!-- Purchase Date -->
                    <div
                        class="bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 font-semibold flex items-center" title="Tanggal pembelian">
                            <i class="fas fa-calendar-alt mr-2"></i> Purchase Date
                        </p>
                        <p class="text-lg text-gray-800">
                            {{ $sparepart->purchase_date ? \Carbon\Carbon::parse($sparepart->purchase_date)->format('d-m-Y') : 'Tidak ada' }}
                        </p>
                    </div>

                    <!-- Delivery Date -->
                    <div class="bg-blue-50 p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 font-semibold flex items-center" title="Tanggal pengiriman">
                            <i class="fas fa-truck-loading mr-2"></i> Delivery Date
                        </p>
                        <p class="text-lg text-gray-800">
                            {{ $sparepart->delivery_date ? \Carbon\Carbon::parse($sparepart->delivery_date)->format('d-m-Y') : 'Tidak ada' }}
                        </p>
                    </div>

                    <!-- PO Number -->
                    <div class="bg-green-50 p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 font-semibold flex items-center" title="Nomor PO">
                            <i class="fas fa-file-alt mr-2"></i> PO Number
                        </p>
                        <p class="text-lg text-gray-800">{{ $sparepart->po_number }}</p>
                    </div>

                    <!-- QR Code -->
                    <div
                        class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow flex items-center justify-center col-span-1 md:col-span-2">
                        @if ($sparepart->qr_code && file_exists(storage_path('app/public/' . $sparepart->qr_code)))
                            <img src="{{ asset('storage/' . $sparepart->qr_code) }}" alt="QR Code"
                                class="w-32 h-32 object-contain border-2 border-gray-300 rounded-lg hover:shadow-md transition-shadow">
                        @else
                            <div class="text-center">
                                <p class="text-red-500">QR Code belum di-generate.</p>
                                <a href="{{ route('sparepart.regenerateQrCode', $sparepart->hashid) }}"
                                    class="mt-2 inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors">
                                    <i class="fas fa-sync mr-1"></i> Generate Sekarang
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex flex-col md:flex-row gap-4 justify-center">
                    <a href="{{ route('sparepart.pdf', $sparepart->hashid) }}"
                        class="bg-red-500 text-white px-6 py-3 rounded-lg hover:bg-red-600 text-center transition-colors flex-1 md:flex-none flex items-center justify-center">
                        <i class="fas fa-download mr-2"></i> Download PDF
                    </a>
                    <a href="{{ route('sparepart.index') }}"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg shadow-md transition-colors flex-1 md:flex-none flex items-center justify-center">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
