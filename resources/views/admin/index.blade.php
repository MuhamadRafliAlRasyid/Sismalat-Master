@extends('layouts.app')

@section('title', 'Daftar User')

@section('content')
    <div class="max-w-7xl mx-auto px-4">
        {{-- Header & Tambah --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                <i class="fas fa-users text-amber-500"></i> Daftar User
            </h2>
            <a href="{{ route('admin.create') }}"
                class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl font-medium flex items-center gap-2 transition shadow-md shadow-amber-200">
                <i class="fas fa-user-plus"></i> Tambah User
            </a>
        </div>

        {{-- Alert Sukses --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)"
                class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-3 rounded-xl flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        {{-- Search --}}
        <form method="GET" class="mb-8">
            <div class="relative max-w-md">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama, email, atau bagian..."
                    class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 pl-11 bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition">
                <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
            </div>
        </form>

        {{-- Grid Kartu User --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse ($users as $user)
                <div
                    class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-2xl shadow-md border border-amber-100 hover:shadow-xl hover:border-amber-300 transition-all duration-300 p-6 flex flex-col items-center text-center group">
                    {{-- Foto Profil --}}
                    <div class="relative mb-4">
                        <img src="{{ $user->profile_photo_path ? asset('images/profile/' . $user->profile_photo_path) : asset('images/avatar.png') }}"
                            alt="{{ $user->name }}"
                            class="w-24 h-24 rounded-full object-cover border-4 border-amber-100 group-hover:border-amber-300 transition-all duration-300 shadow-sm"
                            onerror="this.src='{{ asset('images/avatar.png') }}'">
                        {{-- Role badge kecil di pojok foto --}}
                        <span
                            class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full border-2 border-white flex items-center justify-center text-xs font-bold shadow
                        @if ($user->role === 'admin') bg-purple-500 text-white
                        @elseif($user->role === 'super') bg-red-500 text-white
                        @else bg-blue-500 text-white @endif"
                            title="{{ ucfirst($user->role) }}">
                            @if ($user->role === 'admin')
                                A
                            @elseif($user->role === 'super')
                                S
                            @else
                                K
                            @endif
                        </span>
                    </div>

                    {{-- Nama --}}
                    <h3 class="font-bold text-gray-800 text-lg mb-1">{{ $user->name }}</h3>

                    {{-- Email --}}
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-2 break-all">{{ $user->email }}</p>

                    {{-- Bagian --}}
                    <p class="text-gray-400 text-sm mb-3">
                        <i class="fas fa-building text-amber-400 mr-1"></i>
                        {{ $user->bagian?->nama ?? '-' }}
                    </p>

                    {{-- Role Badge --}}
                    <span
                        class="px-3 py-1 text-xs font-semibold rounded-full mb-4
                    @if ($user->role === 'admin') bg-purple-100 text-purple-800
                    @elseif($user->role === 'super') bg-red-100 text-red-800
                    @else bg-blue-100 text-blue-800 @endif">
                        {{ ucfirst($user->role) }}
                    </span>

                    {{-- Aksi --}}
                    <div class="flex gap-2 mt-auto">
                        <a href="{{ route('admin.edit', $user->hashid) }}"
                            class="flex-1 bg-amber-100 text-amber-700 hover:bg-amber-200 px-3 py-2 rounded-xl text-sm font-medium transition flex items-center justify-center gap-1"
                            title="Edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('admin.destroy', $user->hashid) }}" method="POST"
                            onsubmit="return confirm('Yakin ingin menghapus user {{ $user->name }}?');" class="flex-1">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="w-full bg-red-100 text-red-700 hover:bg-red-200 px-3 py-2 rounded-xl text-sm font-medium transition flex items-center justify-center gap-1"
                                title="Hapus">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16 text-gray-400">
                    <i class="fas fa-users text-5xl mb-4 block"></i>
                    <p class="text-lg">Belum ada data user.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-10 flex justify-center">
            {{ $users->links() }}
        </div>
    </div>
@endsection
