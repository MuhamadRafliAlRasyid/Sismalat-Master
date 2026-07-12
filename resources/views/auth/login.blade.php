{{-- resources/views/auth/login.blade.php --}}
{{-- File mandiri tanpa extends, sudah termasuk layout, dark mode, dan styling --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="{ darkMode: $persist(false).as('darkMode') }"
    :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Tanaoroshi</title>

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Sora', 'Space Grotesk', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    {{-- Font & Icons --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700&family=Space+Grotesk:wght@400;500;600&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('images/logos.jpg') }}" />

    @livewireStyles

    <style>
        /* Animasi shake untuk error */


        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-4px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(4px);
            }
        }

        .animate-shake {
            animation: shake 0.5s ease-in-out;
        }
    </style>
</head>

<body
    class="min-h-screen font-sans antialiased transition-colors duration-300
             bg-gradient-to-br from-amber-50 via-white to-amber-100
             dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 text-gray-800 dark:text-gray-100"
    x-data="{ showPassword: false }">

    {{-- Dark Mode Toggle --}}
    <div class="fixed top-4 right-4 z-50">
        <button @click="darkMode = !darkMode"
            class="p-2.5 rounded-full bg-white dark:bg-gray-800 border border-amber-200 dark:border-gray-600
                   text-gray-600 dark:text-gray-300 hover:bg-amber-50 dark:hover:bg-gray-700 transition shadow-md"
            aria-label="Toggle dark mode">
            <i class="fas text-lg" :class="darkMode ? 'fa-sun' : 'fa-moon'"></i>
        </button>
    </div>

    {{-- Loading Screen --}}
    <div x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 300)" x-show="loading" x-transition.opacity.duration.500
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-white dark:bg-gray-800">
        <div class="w-10 h-10 border-4 border-amber-200 border-t-amber-500 rounded-full animate-spin"></div>
    </div>

    {{-- Main Content --}}
    <main class="relative z-10 flex items-center justify-center min-h-screen p-4 sm:p-6 lg:p-8">
        <div
            class="w-full max-w-5xl grid grid-cols-1 lg:grid-cols-2 bg-white dark:bg-gray-800 rounded-3xl shadow-2xl shadow-amber-100/50 dark:shadow-black/20 overflow-hidden border border-amber-200/60 dark:border-gray-700">

            {{-- ========== PANEL KIRI: INFORMASI APLIKASI ========== --}}
            <div
                class="relative bg-gradient-to-br from-white to-amber-50 dark:from-gray-800 dark:to-gray-800 p-8 sm:p-10 lg:p-12 flex flex-col justify-center">
                {{-- Dekorasi lingkaran --}}
                <div
                    class="absolute -top-20 -left-20 w-64 h-64 bg-amber-200/20 dark:bg-amber-900/10 rounded-full blur-3xl pointer-events-none">
                </div>
                <div
                    class="absolute -bottom-16 -right-16 w-48 h-48 bg-blue-200/20 dark:bg-blue-900/10 rounded-full blur-3xl pointer-events-none">
                </div>

                <div class="relative max-w-md">
                    {{-- Logo & Brand --}}
                    <div class="flex items-center gap-4 mb-8">
                        <div
                            class="bg-white dark:bg-gray-700/80 backdrop-blur-sm p-2.5 rounded-2xl shadow-sm border border-amber-200/60 dark:border-gray-600">
                            <img src="https://perindag.slemankab.go.id/wp-content/uploads/2025/09/Logo-Metrologi-Diedit.png"
                                alt="Logo Tanaoroshi" class="w-16 h-16 object-contain">
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800 dark:text-white tracking-tight">Tanaoroshi</h1>
                            <p class="text-amber-700 dark:text-amber-400 text-sm font-medium">Sistem Inventaris</p>
                        </div>
                    </div>

                    <h2 class="text-2xl sm:text-3xl font-semibold text-gray-800 dark:text-white leading-tight mb-4">
                        Kelola Inventaris Sparepart & Alat<br>
                        <span class="text-amber-600 dark:text-amber-400">Disperindag Kabupaten Karawang</span>
                    </h2>

                    <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed mb-8">
                        Aplikasi modern untuk mencatat pengambilan, pengembalian, purchase request,
                        serta memantau stok secara real-time dengan dukungan scan QR Code.
                    </p>

                    {{-- Fitur Poin --}}
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="flex items-start gap-2">
                            <div
                                class="w-5 h-5 bg-amber-100 dark:bg-amber-900/50 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5 text-amber-600 dark:text-amber-400 text-xs">
                                ✅</div>
                            <div>
                                <p class="font-semibold text-gray-700 dark:text-gray-200">Stok Real‑time</p>
                                <p class="text-gray-500 dark:text-gray-400 text-xs">Update otomatis</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <div
                                class="w-5 h-5 bg-amber-100 dark:bg-amber-900/50 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5 text-amber-600 dark:text-amber-400 text-xs">
                                📱</div>
                            <div>
                                <p class="font-semibold text-gray-700 dark:text-gray-200">QR Code Scan</p>
                                <p class="text-gray-500 dark:text-gray-400 text-xs">Pengambilan cepat</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <div
                                class="w-5 h-5 bg-amber-100 dark:bg-amber-900/50 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5 text-amber-600 dark:text-amber-400 text-xs">
                                📊</div>
                            <div>
                                <p class="font-semibold text-gray-700 dark:text-gray-200">Laporan Lengkap</p>
                                <p class="text-gray-500 dark:text-gray-400 text-xs">PDF & Excel</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <div
                                class="w-5 h-5 bg-amber-100 dark:bg-amber-900/50 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5 text-amber-600 dark:text-amber-400 text-xs">
                                🔒</div>
                            <div>
                                <p class="font-semibold text-gray-700 dark:text-gray-200">Aman & Terintegrasi</p>
                                <p class="text-gray-500 dark:text-gray-400 text-xs">Role-based access</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========== PANEL KANAN: FORM LOGIN (AMBER SOLID) ========== --}}
            <div
                class="bg-amber-500 dark:bg-gray-800 p-8 sm:p-10 lg:p-12 flex flex-col justify-center border-l border-amber-100/60 dark:border-gray-700">
                <div class="max-w-sm mx-auto w-full">
                    <div class="mb-8 text-center">
                        <h2 class="text-2xl font-bold text-white">Selamat Datang Kembali</h2>
                        <p class="text-amber-100 dark:text-gray-400 mt-1 text-sm">Masuk untuk melanjutkan ke sistem</p>
                    </div>

                    {{-- Error Messages --}}
                    @if ($errors->any())
                        <div
                            class="mb-5 bg-red-100 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-xl text-sm flex items-start gap-2 animate-shake">
                            <i class="fas fa-circle-exclamation mt-0.5 text-red-500"></i>
                            <ul class="list-disc list-inside space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('login') }}" method="POST" class="space-y-5">
                        @csrf

                        {{-- Email --}}
                        <div>
                            <label for="email"
                                class="block text-sm font-medium text-white dark:text-gray-300 mb-1.5">Alamat
                                Email</label>
                            <div class="relative">
                                <span
                                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-white/70 dark:text-gray-400 pointer-events-none">
                                    <i class="fas fa-envelope text-sm"></i>
                                </span>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                                    autofocus
                                    class="w-full pl-10 pr-4 py-3 bg-white/90 dark:bg-gray-700 border border-amber-300/60 dark:border-gray-600 rounded-xl text-sm text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500
                                              focus:outline-none focus:ring-2 focus:ring-white/60 dark:focus:ring-amber-400/60 focus:border-white dark:focus:border-amber-500 focus:bg-white dark:focus:bg-gray-700 transition duration-200"
                                    placeholder="nama@email.com">
                            </div>
                        </div>

                        {{-- Password --}}
                        <div>
                            <label for="password"
                                class="block text-sm font-medium text-white dark:text-gray-300 mb-1.5">Kata
                                Sandi</label>
                            <div class="relative">
                                <span
                                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-white/70 dark:text-gray-400 pointer-events-none">
                                    <i class="fas fa-lock text-sm"></i>
                                </span>
                                <input id="password" name="password" :type="showPassword ? 'text' : 'password'"
                                    required
                                    class="w-full pl-10 pr-12 py-3 bg-white/90 dark:bg-gray-700 border border-amber-300/60 dark:border-gray-600 rounded-xl text-sm text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500
                                              focus:outline-none focus:ring-2 focus:ring-white/60 dark:focus:ring-amber-400/60 focus:border-white dark:focus:border-amber-500 focus:bg-white dark:focus:bg-gray-700 transition duration-200"
                                    placeholder="••••••••">
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-amber-800 dark:text-gray-400 hover:text-amber-900 dark:hover:text-amber-300 transition-colors p-1 bg-amber-200/80 dark:bg-gray-600/50 rounded-full w-8 h-8 flex items-center justify-center"
                                    :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                                    <i class="fas text-sm" :class="showPassword ? 'fa-eye' : 'fa-eye-slash'"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Remember & Forgot --}}
                        <div class="flex items-center justify-between text-sm">
                            <label
                                class="flex items-center gap-2 text-white/90 dark:text-gray-400 cursor-pointer select-none">
                                <input type="checkbox" name="remember"
                                    class="w-4 h-4 rounded border-white/60 dark:border-gray-600 text-amber-500 focus:ring-white/50 dark:focus:ring-amber-400/60 bg-white/90 dark:bg-gray-700 cursor-pointer">
                                <span>Ingat saya</span>
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                    class="text-white dark:text-amber-400 hover:text-amber-200 dark:hover:text-amber-300 font-medium transition-colors">
                                    Lupa sandi?
                                </a>
                            @endif
                        </div>

                        {{-- Tombol Login --}}
                        <button type="submit"
                            class="w-full bg-white dark:bg-amber-600 hover:bg-amber-50 dark:hover:bg-amber-700 text-amber-600 dark:text-white font-semibold py-3 rounded-xl
                                       transition duration-200 flex items-center justify-center gap-2 shadow-md shadow-amber-700/20 dark:shadow-amber-900/30
                                       hover:shadow-lg dark:hover:shadow-amber-800/40 focus:outline-none focus:ring-2 focus:ring-white dark:focus:ring-amber-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Masuk ke Sistem</span>
                        </button>
                    </form>

                    {{-- Divider --}}
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-white/30 dark:border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center">
                            <span
                                class="px-4 bg-amber-500 dark:bg-gray-800 text-xs text-white/80 dark:text-gray-400 font-medium">atau</span>
                        </div>
                    </div>

                    {{-- Google Login --}}
                    <a href="{{ route('google.redirects') }}"
                        class="w-full flex items-center justify-center gap-3 py-3 border border-white/30 dark:border-gray-600 rounded-xl text-sm font-medium text-white dark:text-gray-300
                              hover:bg-white/10 dark:hover:bg-gray-700 transition duration-200 bg-transparent dark:bg-gray-800">
                        <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 48 48">
                            <path fill="#EA4335"
                                d="M24 9.5c3.5 0 6.7 1.2 9.2 3.6l6.9-6.9C35.7 2.1 30.2 0 24 0 14.6 0 6.4 5.5 2.5 13.5l8 6.2C12.3 13.1 17.7 9.5 24 9.5z" />
                            <path fill="#4285F4"
                                d="M46.1 24.5c0-1.6-.1-2.7-.4-3.9H24v7.4h12.7c-.3 1.8-1.8 4.6-5.1 6.5l7.8 6c4.6-4.3 7.3-10.5 7.3-16z" />
                            <path fill="#FBBC05"
                                d="M10.5 28.3c-.6-1.8-.9-3.6-.9-5.3s.3-3.5.9-5.3l-8-6.2C.9 14.6 0 18.2 0 22s.9 7.4 2.5 10.5l8-6.2z" />
                            <path fill="#34A853"
                                d="M24 48c6.2 0 11.4-2 15.2-5.5l-7.8-6c-2.1 1.5-4.8 2.5-7.4 2.5-6.3 0-11.7-3.6-13.5-8.9l-8 6.2C6.4 42.5 14.6 48 24 48z" />
                        </svg>
                        <span>Masuk dengan Google</span>
                    </a>

                    {{-- Footer --}}
                    <div class="text-center mt-8">
                        <p class="text-xs text-white/70 dark:text-gray-500">
                            &copy; {{ date('Y') }} Dinas Perindustrian dan Perdagangan<br>Kabupaten Karawang
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @livewireScripts
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
</body>

</html>
