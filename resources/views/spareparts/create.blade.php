@extends('layouts.app')

@section('title', 'Create Sparepart')

@section('content')
    <div class="flex items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-800 py-6">
        <div class="w-full max-w-2xl">
            <h2 class="text-3xl font-bold text-green-700 mb-6 text-center border-b-2 border-green-200 pb-2">
                <i class="fas fa-plus-circle mr-2"></i> Tambah Sparepart Baru
            </h2>

            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-md text-center">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('sparepart.store') }}" method="POST"
                class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 p-6 rounded-lg shadow-lg space-y-4"
                id="sparepartForm">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="nama_part" class="block text-sm font-medium text-gray-700">Nama Part</label>
                        <input type="text" id="nama_part" name="nama_part" required
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                    </div>
                    <div class="form-group">
                        <label for="model" class="block text-sm font-medium text-gray-700">Model</label>
                        <input type="text" id="model" name="model" required
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                    </div>
                    <div class="form-group">
                        <label for="merk" class="block text-sm font-medium text-gray-700">Merk</label>
                        <input type="text" id="merk" name="merk" required
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                    </div>
                    <div class="form-group">
                        <label for="jumlah_baru" class="block text-sm font-medium text-gray-700">Jumlah Baru</label>
                        <input type="number" id="jumlah_baru" name="jumlah_baru" required
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                    </div>
                    <div class="form-group">
                        <label for="jumlah_bekas" class="block text-sm font-medium text-gray-700">Jumlah Bekas</label>
                        <input type="number" id="jumlah_bekas" name="jumlah_bekas" required
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                    </div>
                    <div class="form-group">
                        <label for="supplier" class="block text-sm font-medium text-gray-700">Supplier</label>
                        <input type="text" id="supplier" name="supplier" required
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                    </div>
                    <div class="form-group">
                        <label for="patokan_harga" class="block text-sm font-medium text-gray-700">Patokan Harga</label>
                        <input type="text" id="patokan_harga" name="patokan_harga" required
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                            placeholder="Masukkan angka saja">
                    </div>
                    <div class="form-group">
                        <label for="total" class="block text-sm font-medium text-gray-700">Total</label>
                        <input type="text" id="total" name="total" required
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                            placeholder="Masukkan angka saja">
                    </div>
                    <div class="form-group">
                        <label for="ruk_no" class="block text-sm font-medium text-gray-700">RUK No</label>
                        <input type="text" id="ruk_no" name="ruk_no" required
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                    </div>
                    <div class="form-group">
                        <label for="purchase_date" class="block text-sm font-medium text-gray-700">Purchase Date</label>
                        <input type="date" id="purchase_date" name="purchase_date" required
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                    </div>
                    <div class="form-group">
                        <label for="delivery_date" class="block text-sm font-medium text-gray-700">Delivery Date</label>
                        <input type="date" id="delivery_date" name="delivery_date" required
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                    </div>
                    <div class="form-group">
                        <label for="po_number" class="block text-sm font-medium text-gray-700">PO Number</label>
                        <input type="text" id="po_number" name="po_number" required
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                    </div>
                    <div class="form-group">
                        <label for="titik_pesanan" class="block text-sm font-medium text-gray-700">Titik Pesanan</label>
                        <input type="text" id="titik_pesanan" name="titik_pesanan" required
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                    </div>
                    <div class="form-group">
                        <label for="jumlah_pesanan" class="block text-sm font-medium text-gray-700">Jumlah Pesanan</label>
                        <input type="number" id="jumlah_pesanan" name="jumlah_pesanan" required
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                    </div>
                    <div class="form-group">
                        <label for="cek" class="block text-sm font-medium text-gray-700">Cek</label>
                        <select id="cek" name="cek" required
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                            <option value="1">Ya</option>
                            <option value="0">Tidak</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pic" class="block text-sm font-medium text-gray-700">PIC</label>
                        <input type="text" id="pic" name="pic" required
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                    </div>
                    <div class="form-group">
                        <label for="qr_code" class="block text-sm font-medium text-gray-700"></label>
                        <input type="hidden" id="qr_code" name="qr_code"
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <button type="submit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-md shadow-md transition duration-300 ease-in-out transform hover:-translate-y-1 hover:shadow-lg">
                        <i class="fas fa-save mr-2"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const formatRupiah = (input, initialValue) => {
                    let value = input.value.replace(/[^0-9]/g, ''); // Hanya ambil angka
                    if (value === '') value = initialValue.replace(/[^0-9]/g,
                        ''); // Kembali ke nilai awal jika kosong
                    let number = parseInt(value) || parseInt(initialValue.replace(/[^0-9]/g, '')) || 0;
                    input.value = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(number).replace('Rp', 'Rp ');
                };

                const unformatRupiah = (input) => {
                    let value = input.value.replace(/[^0-9]/g, ''); // Hanya ambil angka
                    if (value === '') value = '0';
                    input.value = value; // Mengembalikan ke format numerik sebelum submit
                };

                // Inisialisasi dengan nilai awal
                const priceInputs = [{
                        id: 'patokan_harga',
                        initial: '1000000'
                    },
                    {
                        id: 'total',
                        initial: '2000000'
                    }
                ];

                priceInputs.forEach(inputConfig => {
                    const input = document.getElementById(inputConfig.id);
                    // Set nilai awal saat halaman dimuat
                    formatRupiah(input, `Rp ${inputConfig.initial}`);
                    input.addEventListener('input', () => formatRupiah(input, `Rp ${inputConfig.initial}`));
                    input.addEventListener('blur', () => {
                        if (input.value === 'Rp 0') input.value = `Rp ${inputConfig.initial}`;
                    });
                    input.addEventListener('focus', () => {
                        if (input.value === `Rp ${inputConfig.initial}`) input.value = '';
                    });
                });

                document.getElementById('sparepartForm').addEventListener('submit', (e) => {
                    priceInputs.forEach(inputConfig => {
                        const input = document.getElementById(inputConfig.id);
                        unformatRupiah(input);
                    });
                });
            });
        </script>
    @endpush
@endsection
