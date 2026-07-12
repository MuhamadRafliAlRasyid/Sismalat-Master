@extends('layouts.app')

@section('title', 'Daftar Purchase Request')

@section('content')
    <main class="p-6 flex flex-col items-center space-y-8 bg-gradient-to-br from-gray-100 to-white min-h-[calc(100vh-4rem)]">
        <section
            class="w-full max-w-6xl bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-xl shadow-2xl p-6 transform transition-all duration-300 hover:shadow-3xl">
            <h1 class="text-3xl font-bold text-indigo-800 mb-6 border-b-2 border-indigo-200 pb-3 flex items-center">
                <i class="fas fa-list-alt mr-2 text-indigo-600"></i> Daftar Purchase Request
            </h1>

            @if (session('success'))
                <div
                    class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-md text-center animate-fade-in">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Search and Actions -->
            <div class="mb-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0 md:space-x-4">
                <!-- Search Form -->
                <form action="{{ route('purchase_requests.index') }}" method="GET" class="w-full md:w-auto flex-1">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama part, status, atau user..."
                            class="w-full border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm pr-10 placeholder-gray-400"
                            aria-label="Cari purchase request">
                        <button type="submit"
                            class="absolute right-2 top-2 text-gray-500 dark:text-gray-400 hover:text-indigo-600 focus:outline-none">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <!-- Action Buttons -->
                <div class="flex space-x-4">
                    <a href="{{ route('purchase_requests.create') }}"
                        class="bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg shadow-md hover:bg-blue-700 transition transform hover:scale-105 flex items-center group"
                        title="Tambah permintaan pembelian baru">
                        <i class="fas fa-plus mr-2"></i> Ajukan Pembelian
                    </a>
                    <a href="{{ route('purchase_requests.unduh') }}"
                        class="bg-gray-600 text-white px-4 py-2 rounded-lg shadow-md hover:bg-gray-700 transition transform hover:scale-105 flex items-center group"
                        title="Unduh data ke Excel">
                        <i class="fas fa-file-excel mr-2"></i> Unduh Excel
                    </a>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-gray-700 border-collapse">
                    <thead class="bg-gray-100 dark:bg-gray-800 text-xs uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-3 text-center border-b">No</th>
                            <th class="px-4 py-3 border-b">Nama User</th>
                            <th class="px-4 py-3 border-b">Nama Part</th>
                            <th class="px-4 py-3 border-b">Part Number</th>
                            <th class="px-4 py-3 border-b">Link Website</th>
                            <th class="px-4 py-3 border-b">Waktu Request</th>
                            <th class="px-4 py-3 border-b">Quantity</th>
                            <th class="px-4 py-3 border-b">Satuan</th>
                            <th class="px-4 py-3 border-b">Masa Delivery</th>
                            <th class="px-4 py-3 border-b">Keterangan</th>
                            <th class="px-4 py-3 border-b">PIC</th>
                            <th class="px-4 py-3 border-b">Quotation Lead Time</th>
                            <th class="px-4 py-3 border-b">Status</th>
                            <th class="px-4 py-3 text-center border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchaseRequests as $index => $item)
                            <tr
                                class="border-b hover:bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 transition-all duration-200">
                                <td class="px-4 py-3 text-center">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">{{ $item->user->name ?? 'Unknown' }}</td>
                                <td class="px-4 py-3 truncate max-w-xs">{{ $item->nama_part }}</td>
                                <td class="px-4 py-3 truncate max-w-xs">{{ $item->part_number }}</td>
                                <td class="px-4 py-3">
                                    @if ($item->link_website)
                                        <a href="{{ $item->link_website }}" target="_blank"
                                            class="text-blue-600 hover:underline truncate max-w-xs block">
                                            Lihat Website
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $item->waktu_request }}</td>
                                <td class="px-4 py-3">{{ $item->quantity }}</td>
                                <td class="px-4 py-3">{{ $item->satuan }}</td>
                                <td class="px-4 py-3">{{ $item->mas_deliver }}</td>
                                <td class="px-4 py-3 truncate max-w-xs">{{ $item->untuk_apa }}</td>
                                <td class="px-4 py-3">{{ $item->pic }}</td>
                                <td class="px-4 py-3">{{ $item->quotation_lead_time ?? 'N/A' }}</td>
                                <td class="px-4 py-3 {{ $item->status === 'PO' ? 'text-green-600' : 'text-yellow-600' }}">
                                    {{ $item->status }}
                                </td>
                                <td class="px-4 py-3 text-center flex justify-center space-x-2">
                                    <a href="{{ route('purchase_requests.show', $item->hashid) }}"
                                        class="bg-indigo-100 text-indigo-600 px-3 py-1 rounded-full text-sm font-medium hover:bg-indigo-200 transition-all duration-200 transform hover:scale-105 relative group"
                                        title="Lihat detail pengajuan">
                                        <i class="fas fa-eye mr-1"></i> Detail
                                    </a>
                                    <a href="{{ route('purchase_requests.edit', $item->hashid) }}"
                                        class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-sm font-medium hover:bg-blue-200 transition-all duration-200 transform hover:scale-105 relative group"
                                        title="Ubah pengajuan">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                    <form action="{{ route('purchase_requests.destroy', $item->hashid) }}" method="POST"
                                        onsubmit="return confirm('Yakin hapus data ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-sm font-medium hover:bg-red-200 transition-all duration-200 transform hover:scale-105 relative group"
                                            title="Hapus pengajuan">
                                            <i class="fas fa-trash mr-1"></i> Hapus
                                        </button>
                                    </form>
                                    @if (Auth::user()->role === 'super' && $item->status === 'PR')
                                        <form action="{{ route('purchase_requests.approve', $item->hashid) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="bg-green-100 text-green-600 px-3 py-1 rounded-full text-sm font-medium hover:bg-green-200 transition-all duration-200 transform hover:scale-105 relative group"
                                                title="Setujui pengajuan">
                                                <i class="fas fa-check mr-1"></i> Setujui
                                            </button>
                                        </form>
                                        <form action="{{ route('purchase_requests.reject', $item->hashid) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="notes" value="Rejected">
                                            <button type="submit"
                                                class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-sm font-medium hover:bg-red-200 transition-all duration-200 transform hover:scale-105 relative group"
                                                title="Tolak pengajuan">
                                                <i class="fas fa-times mr-1"></i> Tolak
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada
                                    data purchase
                                    request.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 text-center">
                {{ $purchaseRequests->links('pagination::tailwind') }}
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
    </style>
@endsection
