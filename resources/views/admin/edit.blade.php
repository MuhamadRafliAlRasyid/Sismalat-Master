@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div
            class="bg-white dark:bg-gray-800 dark:bg-gray-800 dark:bg-gray-800 rounded-3xl shadow-xl border border-amber-100 p-8">
            <div class="flex items-center gap-3 mb-8">
                <i class="fas fa-edit text-3xl text-amber-600"></i>
                <h2 class="text-2xl font-bold text-gray-800">Edit User</h2>
            </div>

            <form method="POST" action="{{ route('admin.update', $user->hashid) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Foto Profil --}}
                <div class="flex items-center gap-6 mb-8">
                    <img id="preview_photo"
                        src="{{ $user->profile_photo_path ? asset('images/profile/' . $user->profile_photo_path) : asset('images/avatar.png') }}"
                        class="w-20 h-20 rounded-2xl object-cover border-2 border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700"
                        alt="Preview" />
                    <label for="profile_photo"
                        class="flex items-center gap-2 bg-amber-50 text-amber-700 px-4 py-2.5 rounded-xl hover:bg-amber-100 cursor-pointer transition font-medium text-sm">
                        <i class="fas fa-camera"></i> Ganti Foto
                    </label>
                    <input id="profile_photo" name="profile_photo" type="file" class="hidden" accept="image/*">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span
                                class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition">
                    </div>

                    <div x-data="{ show: false }">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Password (kosongkan jika tidak
                            diubah)</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password"
                                class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition"
                                placeholder="Password baru (opsional)">
                            <button type="button" @click="show = !show"
                                class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                                <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    @if (auth()->user()->role === 'admin')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Bagian</label>
                            <select name="bagian_id"
                                class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition">
                                <option value="">-- Pilih Bagian --</option>
                                @foreach ($bagians as $bagian)
                                    <option value="{{ $bagian->id }}"
                                        {{ old('bagian_id', $user->bagian_id) == $bagian->id ? 'selected' : '' }}>
                                        {{ $bagian->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Role <span
                                    class="text-red-500">*</span></label>
                            <select name="role" required
                                class="w-full border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition">
                                @foreach (['admin', 'karyawan', 'super'] as $role)
                                    <option value="{{ $role }}"
                                        {{ old('role', $user->role) == $role ? 'selected' : '' }}>
                                        {{ ucfirst($role) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <div class="mt-10 flex gap-4 justify-end">
                    <a href="{{ route('admin.index') }}"
                        class="px-6 py-3 border border-gray-200 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 dark:border-gray-700 hover:bg-gray-50 dark:bg-gray-900 dark:bg-gray-900 font-medium rounded-xl transition flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                    <button type="submit"
                        class="px-8 py-3 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl transition shadow-md shadow-amber-200 flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('profile_photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    document.getElementById('preview_photo').src = ev.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
@endpush
