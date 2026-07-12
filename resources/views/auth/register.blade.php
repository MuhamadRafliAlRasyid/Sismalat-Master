@extends('layouts.guest')

@section('title', 'Daftar')
@section('subtitle', 'Buat akun baru untuk mengelola sparepart')

@section('content')
    <div class="container">
        <!-- Logo -->
        <div class="logo-wrapper">
            <div class="logo-container">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo" onerror="this.src='{{ asset('images/logo.jpg') }}'">
            </div>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm animate-fade-in">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" id="registerForm"
            class="space-y-5">
            @csrf

            <!-- Nama Lengkap -->
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required>
                <span class="error" id="nameError">Nama wajib diisi.</span>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required>
                <span class="error" id="emailError">Email tidak valid.</span>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Kata Sandi</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" required>
                    <span class="password-toggle" onclick="togglePasswordVisibility('password', 'eyePass')">
                        <i class="fas fa-eye-slash" id="eyePass"></i>
                    </span>
                </div>
                <span class="error" id="passError">Minimal 6 karakter.</span>
            </div>

            <!-- Konfirmasi Password -->
            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Kata Sandi</label>
                <div class="password-wrapper">
                    <input type="password" name="password_confirmation" id="password_confirmation" required>
                    <span class="password-toggle" onclick="togglePasswordVisibility('password_confirmation', 'eyeConfirm')">
                        <i class="fas fa-eye-slash" id="eyeConfirm"></i>
                    </span>
                </div>
                <span class="error" id="confirmError">Password tidak cocok.</span>
            </div>

            <!-- Role -->
            <div class="form-group">
                <label for="role">Peran</label>
                <select name="role" id="role" required class="cursor-pointer">
                    <option value="">Pilih Peran</option>
                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Pengguna</option>
                    <option value="spv" {{ old('role') == 'spv' ? 'selected' : '' }}>Supervisor</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            <!-- Bagian -->
            <div class="form-group">
                <label for="bagian_id">Bagian</label>
                <select name="bagian_id" id="bagian_id" class="cursor-pointer">
                    <option value="">Pilih Bagian (opsional)</option>
                    @forelse($bagians as $bagian)
                        <option value="{{ $bagian->id }}" {{ old('bagian_id') == $bagian->id ? 'selected' : '' }}>
                            {{ $bagian->nama }}
                        </option>
                    @empty
                        <option disabled>Tidak ada bagian tersedia</option>
                    @endforelse
                </select>
            </div>

            <!-- Foto Profil -->
            <div class="form-group">
                <label for="profile_photo">Foto Profil (opsional)</label>
                <input type="file" name="profile_photo" id="profile_photo" accept="image/jpeg,image/jpg,image/png">
                <span class="error" id="photoError">File harus JPG/PNG, maks 2MB.</span>
            </div>

            <!-- Submit -->
            <button type="submit">
                <i class="fas fa-user-plus"></i>
                <span>Daftar Sekarang</span>
            </button>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-indigo-600 text-sm hover:underline">
                    Sudah punya akun? Masuk
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let valid = true;

            // Reset semua error
            document.querySelectorAll('.error').forEach(el => el.style.display = 'none');

            // Nama
            const name = document.getElementById('name').value.trim();
            if (!name) {
                document.getElementById('nameError').style.display = 'block';
                valid = false;
            }

            // Email
            const email = document.getElementById('email').value.trim();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                document.getElementById('emailError').style.display = 'block';
                valid = false;
            }

            // Password
            const pass = document.getElementById('password').value;
            if (pass.length < 6) {
                document.getElementById('passError').style.display = 'block';
                valid = false;
            }

            // Konfirmasi
            const confirm = document.getElementById('password_confirmation').value;
            if (pass !== confirm) {
                document.getElementById('confirmError').style.display = 'block';
                valid = false;
            }

            // Foto
            const file = document.getElementById('profile_photo').files[0];
            if (file) {
                const maxSize = 2 * 1024 * 1024;
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (file.size > maxSize || !validTypes.includes(file.type)) {
                    document.getElementById('photoError').style.display = 'block';
                    valid = false;
                }
            }

            if (valid) {
                this.submit();
            }
        });
    </script>
@endpush
