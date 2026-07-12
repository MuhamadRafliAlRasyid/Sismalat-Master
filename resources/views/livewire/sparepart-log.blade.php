<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Log Sparepart</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 border rounded-lg">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <th class="p-2 border">Tanggal</th>
                    <th class="p-2 border">User</th>
                    <th class="p-2 border">Bagian</th>
                    <th class="p-2 border">Jenis</th>
                    <th class="p-2 border">Jumlah</th>
                    <th class="p-2 border">Dikembalikan</th>
                    <th class="p-2 border">Sisa</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="text-center">
                        <td class="p-2 border">{{ $log['tanggal'] }}</td>
                        <td class="p-2 border">{{ $log['user'] }}</td>
                        <td class="p-2 border">{{ $log['bagian'] }}</td>

                        <td class="p-2 border">
                            @if ($log['jenis'] == 'Pengambilan')
                                <span class="text-blue-600 font-semibold">Ambil</span>
                            @else
                                <span class="text-green-600 font-semibold">Kembali</span>
                            @endif
                        </td>

                        <td class="p-2 border">{{ $log['jumlah'] }}</td>
                        <td class="p-2 border">{{ $log['dikembalikan'] }}</td>

                        <td class="p-2 border">
                            @if ($log['sisa'] !== '-')
                                <span class="text-red-600 font-bold">
                                    {{ $log['sisa'] }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-4 text-center text-gray-500 dark:text-gray-400">
                            Tidak ada data
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
