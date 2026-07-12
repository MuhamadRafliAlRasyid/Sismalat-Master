<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: $persist(false).as('darkMode') }" :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Tanaoroshi - Sistem Manajemen Alat" />
    <meta name="author" content="Your Company" />
    <meta name="robots" content="index, follow" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="view-transition" content="same-origin" />
    <title>@yield('title') Sismalat</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logos.jpg') }}?v={{ time() }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.0/dist/driver.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        gold: '#e6a817',
                        'gold-light': '#f5c842',
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    @livewireStyles
    @stack('styles')

    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed: 80px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #f8fafc;
            -webkit-font-smoothing: antialiased;
        }

        .dark body {
            background: #0f172a;
        }

        @view-transition {
            navigation: auto;
        }

        .sidebar {
            width: var(--sidebar-width);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: calc(100vh - 64px);
            position: fixed;
            top: 64px;
            left: 0;
            z-index: 40;
            background: white;
            border-right: 1px solid rgba(245, 200, 66, 0.15);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.03);
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #e5e7eb #fff;
            transform: translateX(0);
        }

        .dark .sidebar {
            background: #1e293b;
            border-color: rgba(245, 200, 66, 0.1);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.2);
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }

        .sidebar-text,
        .chevron {
            opacity: 1;
            transition: opacity 0.2s ease;
        }

        .sidebar.collapsed .sidebar-text,
        .sidebar.collapsed .chevron {
            opacity: 0;
            width: 0;
            overflow: hidden;
            white-space: nowrap;
            pointer-events: none;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar.collapsed~.main-content {
            margin-left: var(--sidebar-collapsed);
        }

        @media (max-width: 1023px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px !important;
                box-shadow: 8px 0 30px rgba(0, 0, 0, 0.1);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar.collapsed {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0 !important;
            }

            .overlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.3);
                backdrop-filter: blur(4px);
                z-index: 30;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s;
            }

            .overlay.show {
                opacity: 1;
                visibility: visible;
            }
        }

        .menu-item {
            transition: all 0.2s ease;
            border-radius: 12px;
        }

        .menu-item.active {
            background: linear-gradient(135deg, rgba(245, 200, 66, 0.15), rgba(230, 168, 23, 0.08));
            border-right: 3px solid #e6a817;
            font-weight: 600;
            color: #b45309;
        }

        .dark .menu-item.active {
            background: linear-gradient(135deg, rgba(245, 200, 66, 0.2), rgba(230, 168, 23, 0.1));
            color: #fbbf24;
        }

        .menu-item:hover:not(.active) {
            background: rgba(245, 200, 66, 0.08);
        }

        .animate-fade-in {
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .bell-shake {
            animation: ring 0.5s ease-in-out;
        }

        @keyframes ring {
            0% {
                transform: rotate(0);
            }

            25% {
                transform: rotate(8deg);
            }

            50% {
                transform: rotate(-8deg);
            }

            75% {
                transform: rotate(4deg);
            }

            100% {
                transform: rotate(0);
            }
        }

        .preloader {
            position: fixed;
            inset: 0;
            background: white;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.4s, visibility 0.4s;
        }

        .dark .preloader {
            background: #0f172a;
        }

        .preloader.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .spinner {
            width: 48px;
            height: 48px;
            border: 5px solid #fde68a;
            border-top: 5px solid #e6a817;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }

        .collapsed-tooltip {
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.2s ease;
            position: absolute;
            left: 70px;
            background: #1e293b;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            white-space: nowrap;
            pointer-events: none;
            z-index: 100;
        }

        .collapsed-tooltip::after {
            content: '';
            position: absolute;
            top: 50%;
            left: -6px;
            transform: translateY(-50%);
            border-top: 6px solid transparent;
            border-bottom: 6px solid transparent;
            border-right: 6px solid #1e293b;
        }

        .sidebar.collapsed .menu-item:hover .collapsed-tooltip {
            visibility: visible;
            opacity: 1;
        }

        .ripple {
            position: relative;
            overflow: hidden;
        }

        .ripple::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle, currentColor 10%, transparent 10%) no-repeat center;
            transform: scale(10, 10);
            opacity: 0;
            transition: transform 0.5s, opacity 0.3s;
        }

        .ripple:active::after {
            transform: scale(0, 0);
            opacity: 0.2;
            transition: 0s;
        }

        .scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: #e6a817;
            z-index: 9999;
            transition: width 0.2s;
            box-shadow: 0 0 8px rgba(230, 168, 23, 0.6);
        }

        button,
        .menu-item,
        .ripple {
            transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.2s ease;
        }

        button:hover,
        .menu-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .dark button:hover,
        .dark .menu-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        button:active,
        .menu-item:active {
            transform: translateY(0);
        }

        .sidebar.collapsed .menu-item:hover {
            transform: scale(1.05);
        }

        .animate-shrink {
            animation: shrink 5s linear forwards;
        }

        @keyframes shrink {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }

        /* ========== DARK MODE TEXT ========== */
        .dark .animate-fade-in h1,
        .dark .animate-fade-in h2,
        .dark .animate-fade-in h3,
        .dark .animate-fade-in h4,
        .dark .animate-fade-in h5,
        .dark .animate-fade-in h6,
        .dark .animate-fade-in p,
        .dark .animate-fade-in span:not([class*="text-"]),
        .dark .animate-fade-in label:not([class*="text-"]),
        .dark .animate-fade-in td,
        .dark .animate-fade-in th,
        .dark .animate-fade-in li,
        .dark .animate-fade-in blockquote {
            color: #e2e8f0;
        }

        .dark .animate-fade-in .text-gray-900,
        .dark .animate-fade-in .text-gray-800,
        .dark .animate-fade-in .text-gray-700,
        .dark .animate-fade-in .text-gray-600,
        .dark .animate-fade-in .text-gray-500,
        .dark .animate-fade-in .text-gray-400,
        .dark .animate-fade-in .text-gray-300,
        .dark .animate-fade-in .text-gray-200,
        .dark .animate-fade-in .text-gray-100 {
            color: #e2e8f0 !important;
        }

        .dark main.animate-fade-in .text-gray-900,
        .dark main.animate-fade-in .text-gray-800,
        .dark main.animate-fade-in .text-gray-700,
        .dark main.animate-fade-in .text-gray-600,
        .dark main.animate-fade-in .text-gray-500,
        .dark main.animate-fade-in .text-gray-400,
        .dark main.animate-fade-in .text-gray-300,
        .dark main.animate-fade-in .text-gray-200,
        .dark main.animate-fade-in .text-gray-100 {
            color: #e2e8f0 !important;
        }

        .dark .animate-fade-in [class*="bg-gradient"],
        .dark .animate-fade-in [class*="text-transparent"] {
            color: inherit !important;
        }

        .dark [class*="via-white"][class*="bg-gradient"] {
            --tw-gradient-via: #1e293b !important;
        }

        .dark .animate-fade-in a.text-gray-500,
        .dark .animate-fade-in a.text-gray-400 {
            color: #000000 !important;
        }

        .dark .animate-fade-in a.text-gray-500:hover,
        .dark .animate-fade-in a.text-gray-400:hover {
            color: #ffffff !important;
        }

        .dark [class*="from-amber-50"][class*="bg-gradient"] {
            --tw-gradient-from: #0f172a !important;
        }

        .dark [class*="to-orange-50"][class*="bg-gradient"] {
            --tw-gradient-to: #1e293b !important;
        }

        .dark [class*="from-amber-100"][class*="bg-gradient"] {
            --tw-gradient-from: #334155 !important;
        }

        .dark [class*="to-orange-100"][class*="bg-gradient"] {
            --tw-gradient-to: #334155 !important;
        }

        .dark [class*="bg-gradient-to-br"]:not([class*="dark:from-"]):not([class*="dark:via-"]):not([class*="dark:to-"]) {
            background-image: linear-gradient(to bottom right, #1e293b, #0f172a, #1e293b) !important;
        }

        .dark input[type="text"],
        .dark input[type="number"],
        .dark input[type="email"],
        .dark input[type="password"],
        .dark input[type="date"],
        .dark input[type="file"],
        .dark input[type="search"],
        .dark textarea,
        .dark select {
            background-color: #1e293b !important;
            color: #e2e8f0 !important;
            border-color: #4b5563 !important;
        }

        .dark input::placeholder,
        .dark textarea::placeholder {
            color: #9ca3af !important;
        }

        .dark input[type="file"]::-webkit-file-upload-button {
            background-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        .dark .sidebar a,
        .dark .sidebar button,
        .dark .sidebar span:not([class*="text-"]),
        .dark .sidebar div:not([class*="text-"]),
        .dark .sidebar .menu-item {
            color: #cbd5e1;
        }

        /* ── Notifikasi (from component) ── */
        .notif-bell.is-ringing i {
            animation: notif-ring 0.6s ease-in-out;
            transform-origin: 50% 0%;
            display: inline-block;
        }

        @keyframes notif-ring {
            0% {
                transform: rotate(0deg);
            }

            15% {
                transform: rotate(12deg);
            }

            30% {
                transform: rotate(-10deg);
            }

            45% {
                transform: rotate(8deg);
            }

            60% {
                transform: rotate(-6deg);
            }

            75% {
                transform: rotate(4deg);
            }

            90% {
                transform: rotate(-2deg);
            }

            100% {
                transform: rotate(0deg);
            }
        }

        .notif-panel {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(245, 200, 66, 0.18);
            box-shadow:
                0 4px 6px -1px rgba(0, 0, 0, 0.06),
                0 20px 60px -10px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.6) inset;
        }

        .dark .notif-panel {
            background: rgba(30, 41, 59, 0.94);
            border-color: rgba(245, 200, 66, 0.12);
            box-shadow:
                0 4px 6px -1px rgba(0, 0, 0, 0.3),
                0 20px 60px -10px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.04) inset;
        }

        .notif-panel-header {
            background: linear-gradient(135deg,
                    rgba(255, 251, 235, 0.8) 0%,
                    rgba(255, 255, 255, 0) 100%);
            border-bottom: 1px solid rgba(245, 200, 66, 0.12);
        }

        .dark .notif-panel-header {
            background: linear-gradient(135deg,
                    rgba(245, 200, 66, 0.05) 0%,
                    rgba(30, 41, 59, 0) 100%);
            border-bottom-color: rgba(255, 255, 255, 0.06);
        }

        .notif-list {
            scrollbar-width: thin;
            scrollbar-color: #fbbf24 transparent;
        }

        .notif-list::-webkit-scrollbar {
            width: 4px;
        }

        .notif-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .notif-list::-webkit-scrollbar-thumb {
            background: #fbbf24;
            border-radius: 99px;
            opacity: 0.4;
        }

        .dark .notif-list::-webkit-scrollbar-thumb {
            background: #78350f;
        }

        .notif-item:hover .notif-actions {
            opacity: 1 !important;
        }

        .notif-panel-footer {
            background: rgba(249, 250, 251, 0.7);
        }

        .dark .notif-panel-footer {
            background: rgba(15, 23, 42, 0.5);
        }

        .notif-badge {
            animation: badge-pop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes badge-pop {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }

        .notif-flash {
            animation: flash-green 0.5s ease-out;
        }

        @keyframes flash-green {
            0% {
                background-color: rgba(52, 211, 153, 0.15);
            }

            100% {
                background-color: transparent;
            }
        }

        /* ✨ NOTIFIKASI TAMBAHAN - INFO BADGE & URGENT ✨ */
        .notif-item.notif-urgent {
            background: linear-gradient(to right, rgba(239, 68, 68, 0.08), transparent) !important;
            border-left: 3px solid #ef4444 !important;
        }

        .dark .notif-item.notif-urgent {
            background: linear-gradient(to right, rgba(239, 68, 68, 0.15), transparent) !important;
        }

        .notif-item.notif-warning {
            background: linear-gradient(to right, rgba(245, 158, 11, 0.06), transparent) !important;
            border-left: 3px solid #f59e0b !important;
        }

        .dark .notif-item.notif-warning {
            background: linear-gradient(to right, rgba(245, 158, 11, 0.12), transparent) !important;
        }

        .notif-info-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.65rem;
            font-weight: 600;
            line-height: 1;
        }

        .notif-action-btn {
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .notif-action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* ✨ WELCOME CARD KARYAWAN ✨ */
        .welcome-card {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid rgba(245, 200, 66, 0.3);
        }

        .dark .welcome-card {
            background: linear-gradient(135deg, rgba(245, 200, 66, 0.1) 0%, rgba(245, 200, 66, 0.05) 100%);
            border-color: rgba(245, 200, 66, 0.2);
        }

        .quick-action-btn {
            transition: all 0.2s ease;
        }

        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(245, 200, 66, 0.3);
        }
    </style>
</head>

<body x-data="appLayout()" x-init="init()" @keydown.window="handleKeydown($event)"
    class="transition-colors duration-300 dark:bg-gray-900">

    <div class="scroll-progress" :style="'width: ' + scrollProgress + '%'"></div>

    <div x-show="loading" x-transition.opacity.duration.400ms class="preloader">
        <div class="flex flex-col items-center">
            <img src="{{ asset('images/logos.jpg') }}" class="w-16 h-16 animate-bounce" alt="Loading">
            <div class="spinner mt-4"></div>
            <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm">Memuat...</p>
        </div>
    </div>

    <div class="fixed top-4 right-4 z-50 space-y-3 w-80 pointer-events-none">
        <template x-for="(toast, index) in toasts" :key="index">
            <div x-show="true" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-full" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-full"
                :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-red-500'"
                class="text-white px-5 py-4 rounded-xl shadow-lg flex items-start gap-3 pointer-events-auto"
                x-init="setTimeout(() => toasts.splice(index, 1), 5000)">
                <i class="fas text-xl" :class="toast.type === 'success' ? 'fa-check-circle' : 'fa-times-circle'"></i>
                <div class="flex-1">
                    <p x-text="toast.message" class="text-sm font-medium"></p>
                    <div class="w-full h-1 mt-2 bg-white/30 rounded-full overflow-hidden">
                        <div class="h-full bg-white/80 rounded-full animate-shrink"></div>
                    </div>
                </div>
                <button @click="toasts.splice(index, 1)" class="text-white/80 hover:text-white ml-2">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </template>
    </div>

    @auth
        @php $user = Auth::user(); @endphp

        {{-- Overlay mobile hanya jika sidebar ada --}}
        @if ($user->isAdminOrSuper())
            <div x-show="sidebarOpen" @click="sidebarOpen = false" class="overlay" :class="{ 'show': sidebarOpen }"></div>
        @endif

        {{-- Header --}}
        <header
            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md border-b border-amber-100 dark:border-gray-700 fixed w-full z-50 h-16 flex items-center px-4 lg:px-6 shadow-sm transition-colors">
            <div class="flex items-center flex-1 gap-3">
                {{-- Tombol sidebar hanya untuk admin --}}
                @if ($user->isAdminOrSuper())
                    <button @click="collapsed = !collapsed"
                        class="text-gray-500 dark:text-gray-300 hover:bg-amber-50 dark:hover:bg-gray-700 p-2 rounded-lg transition hidden lg:block">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <button @click="toggleSidebar()"
                        class="text-gray-500 dark:text-gray-300 hover:bg-amber-50 dark:hover:bg-gray-700 p-2 rounded-lg transition lg:hidden">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                @endif

                <div class="flex items-center gap-3 ml-2">
                    <img src="{{ asset('images/logos.jpg') }}" class="w-9 h-9 lg:w-10 lg:h-10 object-contain rounded-lg"
                        alt="Logo" />
                    <div class="hidden sm:block">
                        <h1 class="text-lg lg:text-xl font-semibold text-gray-800 dark:text-white">Sistem
                            Manajemen Alat</h1>
                        @unless ($user->isAdminOrSuper())
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 -mt-0.5">Halo, {{ $user->name }} 👋</p>
                        @endunless
                    </div>
                </div>

                {{-- Menu khusus karyawan di navbar --}}
                @unless ($user->isAdminOrSuper())
                    <div class="hidden sm:flex items-center gap-4 ml-6">
                        <a href="{{ route('karyawan.dashboard') }}"
                            class="text-gray-700 dark:text-gray-300 hover:text-amber-600 text-sm font-medium transition {{ request()->routeIs('karyawan.dashboard') ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 px-3 py-1.5 rounded-lg' : '' }}">
                            <i class="fas fa-tachometer-alt mr-1.5"></i>Dashboard
                        </a>
                        <a href="{{ route('alats.index') }}"
                            class="text-gray-700 dark:text-gray-300 hover:text-amber-600 text-sm font-medium transition {{ request()->routeIs('alats.index') ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 px-3 py-1.5 rounded-lg' : '' }}">
                            <i class="fas fa-tools mr-1.5"></i>Alat
                        </a>
                        <a href="{{ route('pengambilan_alat.index') }}"
                            class="text-gray-700 dark:text-gray-300 hover:text-amber-600 text-sm font-medium transition {{ request()->routeIs('pengambilan_alat.*') ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 px-3 py-1.5 rounded-lg' : '' }}">
                            <i class="fas fa-hand-holding mr-1.5"></i>Pengambilan
                        </a>
                        <a href="{{ route('pengembalian_alat.index') }}"
                            class="text-gray-700 dark:text-gray-300 hover:text-amber-600 text-sm font-medium transition {{ request()->routeIs('pengembalian_alat.*') ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 px-3 py-1.5 rounded-lg' : '' }}">
                            <i class="fas fa-undo-alt mr-1.5"></i>Pengembalian
                        </a>
                    </div>
                @endunless
            </div>

            <div class="flex items-center gap-2 lg:gap-3">
                <button id="search-button" @click="openSearch = true"
                    class="p-2 rounded-full hover:bg-amber-50 dark:hover:bg-gray-700 transition text-gray-600 dark:text-gray-300">
                    <i class="fas fa-search"></i>
                    <span class="text-xs ml-1 hidden md:inline opacity-50">Ctrl+K</span>
                </button>

                <a href="{{ route('qr-scanner') }}" id="scan-qr-button"
                    class="p-2 rounded-full hover:bg-amber-50 dark:hover:bg-gray-700 transition text-gray-600 dark:text-gray-300">
                    <i class="fas fa-qrcode"></i>
                </a>

                <div x-show="openSearch" @click.away="openSearch = false" x-transition
                    class="fixed inset-0 z-50 flex items-start justify-center pt-20 bg-black/40 backdrop-blur-sm">
                    <div class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-2xl shadow-2xl p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <i class="fas fa-search text-gray-500"></i>
                            <input type="text" placeholder="Cari halaman atau data..."
                                class="w-full bg-transparent border-0 text-lg focus:outline-none dark:text-white"
                                x-ref="searchInput" x-init="$nextTick(() => $refs.searchInput.focus())" @keydown.escape="openSearch = false">
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <p class="font-medium mb-2">Menu cepat:</p>
                            <a href="{{ route('alats.index') }}" @click="openSearch = false"
                                class="block px-3 py-2 rounded-lg hover:bg-amber-50 dark:hover:bg-gray-700">Data Alat</a>
                            <a href="{{ route('pengambilan_alat.index') }}" @click="openSearch = false"
                                class="block px-3 py-2 rounded-lg hover:bg-amber-50 dark:hover:bg-gray-700">Pengambilan</a>
                            <a href="{{ route('pengembalian_alat.index') }}" @click="openSearch = false"
                                class="block px-3 py-2 rounded-lg hover:bg-amber-50 dark:hover:bg-gray-700">Pengembalian</a>
                            @if ($user->isAdminOrSuper())
                                <a href="{{ route('admin.index') }}" @click="openSearch = false"
                                    class="block px-3 py-2 rounded-lg hover:bg-amber-50 dark:hover:bg-gray-700">User</a>
                            @endif
                            <a href="{{ route('admin.edit', auth()->user()->hashid ?? '') }}" @click="openSearch = false"
                                class="block px-3 py-2 rounded-lg hover:bg-amber-50 dark:hover:bg-gray-700">Akun Saya</a>
                        </div>
                    </div>
                </div>

                <button @click="darkMode = !darkMode"
                    class="p-2 rounded-full hover:bg-amber-50 dark:hover:bg-gray-700 transition text-gray-600 dark:text-gray-300">
                    <i class="fas" :class="darkMode ? 'fa-sun' : 'fa-moon'"></i>
                </button>

                {{-- ★ Notifikasi Interaktif (Premium Redesign + Info Badge) ★ --}}
                @php
                    $notificationsJson = json_encode(
                        auth()->user()->unreadNotifications->take(15)->map(
                            fn($n) => [
                                'id' => $n->id,
                                'icon' => $n->data['icon'] ?? 'bell',
                                'color' => $n->data['color'] ?? 'gray',
                                'priority' => $n->data['priority'] ?? 'normal',
                                'title' => Str::limit(
                                    $n->data['nama_alat'] ?? ($n->data['message'] ?? 'Notifikasi'),
                                    70,
                                ),
                                'message' => $n->data['message'] ?? '',
                                'time' => $n->created_at->diffForHumans(),
                                'createdAt' => $n->created_at->toISOString(),
                                'url' => $n->data['action_url'] ?? '#',
                                'actionLabel' => $n->data['action_label'] ?? 'Lihat Detail',
                                'sisaHari' => $n->data['sisa_hari'] ?? null,
                                'persentase' => $n->data['persentase_sisa'] ?? null,
                                'jatuhTempo' => $n->data['jatuh_tempo'] ?? null,
                                'read' => !is_null($n->read_at),
                            ],
                        ),
                    );
                @endphp

                <div x-data="notificationPanel({{ $notificationsJson }}, '{{ csrf_token() }}')" x-init="init()" class="relative">
                    {{-- ── Bell Button ── --}}
                    <button id="notifications-bell" @click="toggle()" :class="{ 'is-ringing': shouldRing && !open }"
                        class="notif-bell p-2 rounded-full transition-colors text-gray-600 dark:text-gray-300
                               hover:bg-amber-50 dark:hover:bg-gray-700 relative focus:outline-none
                               focus-visible:ring-2 focus-visible:ring-amber-400"
                        aria-label="Buka notifikasi" :aria-expanded="open">
                        <i class="fas fa-bell text-xl"></i>

                        {{-- Badge --}}
                        <span x-show="unreadCount > 0" x-text="unreadCount > 99 ? '99+' : unreadCount"
                            x-transition:enter="transition scale-100 ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-50" x-transition:enter-end="opacity-100 scale-100"
                            :class="{ 'animate-pulse': unreadCount > 0 }"
                            class="notif-badge absolute -top-1 -right-1 min-w-[20px] h-5 px-1
                                   bg-red-500 text-white text-[11px] font-bold rounded-full
                                   flex items-center justify-center shadow-sm leading-none"></span>
                    </button>

                    {{-- ── Dropdown Panel ── --}}
                    <div x-show="open" @click.away="close()" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                        class="notif-panel absolute right-0 mt-3 w-[400px] max-w-[calc(100vw-1rem)]
                               rounded-2xl z-50 overflow-hidden origin-top-right"
                        role="dialog" aria-label="Panel notifikasi">
                        {{-- Header --}}
                        <div class="notif-panel-header px-5 pt-5 pb-3">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/40
                                                flex items-center justify-center">
                                        <i class="fas fa-bell text-amber-600 dark:text-amber-400 text-sm"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-sm text-gray-800 dark:text-white leading-tight">
                                            Notifikasi
                                        </h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-tight mt-0.5">
                                            <span x-show="unreadCount > 0" x-text="unreadCount + ' belum dibaca'"></span>
                                            <span x-show="unreadCount === 0">Semua sudah dibaca</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button @click="markAllAsRead()" x-show="unreadCount > 0"
                                        class="text-xs text-amber-600 dark:text-amber-400 hover:text-amber-800
                                               dark:hover:text-amber-200 font-medium px-2 py-1 rounded-lg
                                               hover:bg-amber-50 dark:hover:bg-amber-900/20 transition">Tandai
                                        semua</button>
                                    <button @click="close()"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg
                                               text-gray-400 hover:text-gray-600 dark:hover:text-gray-200
                                               hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                                        aria-label="Tutup">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Filter Tabs (dengan Urgent) --}}
                            <div class="flex gap-1 p-1 rounded-xl bg-gray-100 dark:bg-gray-700/60">
                                <button @click="activeFilter = 'all'"
                                    :class="activeFilter === 'all'
                                        ?
                                        'bg-white dark:bg-gray-600 text-gray-800 dark:text-white shadow-sm' :
                                        'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                    class="flex-1 text-xs font-medium py-1.5 rounded-lg transition-all duration-200">
                                    Semua
                                    <span class="ml-1 text-[10px] opacity-60"
                                        x-text="'(' + notifications.length + ')'"></span>
                                </button>
                                <button @click="activeFilter = 'urgent'"
                                    :class="activeFilter === 'urgent'
                                        ?
                                        'bg-white dark:bg-gray-600 text-gray-800 dark:text-white shadow-sm' :
                                        'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                    class="flex-1 text-xs font-medium py-1.5 rounded-lg transition-all duration-200">
                                    🔴 Urgent
                                    <span x-show="urgentCount > 0" x-text="'(' + urgentCount + ')'"
                                        class="ml-1 text-[10px] text-red-500 font-semibold"></span>
                                </button>
                                <button @click="activeFilter = 'unread'"
                                    :class="activeFilter === 'unread'
                                        ?
                                        'bg-white dark:bg-gray-600 text-gray-800 dark:text-white shadow-sm' :
                                        'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                    class="flex-1 text-xs font-medium py-1.5 rounded-lg transition-all duration-200">
                                    Belum
                                    <span x-show="unreadCount > 0" x-text="'(' + unreadCount + ')'"
                                        class="ml-1 text-[10px] text-amber-600 dark:text-amber-400 font-semibold"></span>
                                </button>
                            </div>
                        </div>

                        {{-- Notification List --}}
                        <div class="notif-list max-h-[400px] overflow-y-auto" id="notif-scroll-area">
                            {{-- Empty State --}}
                            <div x-show="filteredNotifications.length === 0"
                                class="flex flex-col items-center justify-center py-12 px-6 text-center">
                                <div
                                    class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-700
                                            flex items-center justify-center mb-3">
                                    <i class="fas fa-bell-slash text-2xl text-gray-400 dark:text-gray-500"></i>
                                </div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                    <span x-show="activeFilter === 'unread'">Tidak ada notifikasi belum dibaca</span>
                                    <span x-show="activeFilter === 'urgent'">Tidak ada notifikasi urgent</span>
                                    <span x-show="activeFilter === 'all'">Tidak ada notifikasi</span>
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    Notifikasi baru akan muncul di sini
                                </p>
                            </div>

                            {{-- Items --}}
                            <template x-for="(n, i) in filteredNotifications" :key="n.id">
                                <div class="notif-item-wrapper relative overflow-hidden"
                                    @touchstart="touchStart($event, i)" @touchmove="touchMove($event, i)"
                                    @touchend="touchEnd($event, i)">
                                    {{-- Swipe-to-dismiss reveal layer --}}
                                    <div class="notif-swipe-bg absolute inset-0 flex items-center justify-end pr-5
                                               bg-red-50 dark:bg-red-900/30 transition-opacity duration-200"
                                        :style="'opacity: ' + (swipeProgress[i] || 0)">
                                        <i class="fas fa-trash-alt text-red-400 text-sm"></i>
                                    </div>

                                    {{-- Main item card --}}
                                    <a :href="n.url" @click.prevent="handleItemClick(n)"
                                        :style="'transform: translateX(' + (swipeOffset[i] || 0) + 'px);'"
                                        class="notif-item relative flex flex-col gap-2 px-5 py-3.5
                                               border-b border-gray-100 dark:border-gray-700/50
                                               cursor-pointer transition-transform duration-75 select-none"
                                        :class="[
                                            !n.read && n.priority === 'high' ? 'notif-urgent' : '',
                                            !n.read && n.priority !== 'high' ?
                                            'notif-warning bg-amber-50/40 dark:bg-amber-900/10 hover:bg-amber-50 dark:hover:bg-amber-900/20' :
                                            '',
                                            n.read ?
                                            'bg-white dark:bg-transparent hover:bg-gray-50 dark:hover:bg-gray-700/30' :
                                            ''
                                        ]">

                                        {{-- Row 1: Icon + Title + Actions --}}
                                        <div class="flex items-start gap-3 w-full">
                                            {{-- Icon avatar --}}
                                            <div class="relative flex-shrink-0 mt-0.5">
                                                <div class="w-9 h-9 rounded-xl flex items-center justify-center transition-transform"
                                                    :class="{
                                                        'bg-red-100 dark:bg-red-900/50 text-red-500': n
                                                            .color === 'red',
                                                        'bg-orange-100 dark:bg-orange-900/50 text-orange-500': n
                                                            .color === 'orange',
                                                        'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-500': n
                                                            .color === 'green',
                                                        'bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400':
                                                            !n.read && n.color === 'gray',
                                                        'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400': n
                                                            .read
                                                    }">
                                                    <i :class="'fas fa-' + n.icon + ' text-sm'"></i>
                                                </div>
                                                <span x-show="!n.read"
                                                    class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5
                                                           bg-blue-500 rounded-full border-2
                                                           border-white dark:border-gray-800 shadow-sm"></span>
                                            </div>

                                            {{-- Content --}}
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm leading-snug line-clamp-2 transition-colors"
                                                    :class="!n.read ?
                                                        'font-medium text-gray-800 dark:text-gray-100' :
                                                        'font-normal text-gray-600 dark:text-gray-300'"
                                                    x-text="n.title"></p>
                                                <p x-show="n.message && n.message !== n.title"
                                                    class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-1"
                                                    x-text="n.message"></p>
                                            </div>

                                            {{-- Actions: visible on hover --}}
                                            <div
                                                class="flex items-center gap-1 flex-shrink-0 opacity-0 group-hover:opacity-100
                                                    notif-actions transition-opacity">
                                                <button x-show="!n.read" @click.stop.prevent="markOneAsRead(n)"
                                                    class="w-7 h-7 flex items-center justify-center rounded-lg
                                                           text-gray-400 hover:text-blue-500 hover:bg-blue-50
                                                           dark:hover:bg-blue-900/20 transition"
                                                    title="Tandai sudah dibaca">
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                                <i
                                                    class="fas fa-chevron-right text-[10px] text-gray-300 dark:text-gray-600 ml-1"></i>
                                            </div>
                                        </div>

                                        {{-- Row 2: Info Badges (Sisa Hari, Persentase, Jatuh Tempo) --}}
                                        <div x-show="n.sisaHari !== null || n.persentase !== null || n.jatuhTempo"
                                            class="flex items-center gap-1.5 flex-wrap ml-12">
                                            <span x-show="n.sisaHari !== null" class="notif-info-badge"
                                                :class="{
                                                    'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300': n
                                                        .priority === 'high',
                                                    'bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300': n
                                                        .priority !== 'high'
                                                }">
                                                <i class="fas fa-clock text-[9px]"></i>
                                                <span x-text="n.sisaHari + ' hari lagi'"></span>
                                            </span>
                                            <span x-show="n.persentase !== null"
                                                class="notif-info-badge bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">
                                                <i class="fas fa-chart-pie text-[9px]"></i>
                                                <span x-text="n.persentase + '%'"></span>
                                            </span>
                                            <span x-show="n.jatuhTempo"
                                                class="notif-info-badge bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300">
                                                <i class="fas fa-calendar text-[9px]"></i>
                                                <span x-text="n.jatuhTempo"></span>
                                            </span>
                                        </div>

                                        {{-- Row 3: Action Button + Time --}}
                                        <div class="flex items-center justify-between gap-2 ml-12">
                                            <button @click.stop.prevent="handleItemClick(n)" class="notif-action-btn"
                                                :class="{
                                                    'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white shadow-md shadow-red-200 dark:shadow-red-900': n
                                                        .priority === 'high',
                                                    'bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white shadow-md shadow-amber-200 dark:shadow-amber-900': n
                                                        .priority !== 'high'
                                                }">
                                                <i class="fas fa-undo-alt text-[10px]"></i>
                                                <span x-text="n.actionLabel"></span>
                                            </button>
                                            <div class="flex items-center gap-2">
                                                <span x-show="!n.read"
                                                    class="text-[10px] font-semibold text-amber-600 dark:text-amber-400
                                                             bg-amber-100 dark:bg-amber-900/30 px-1.5 py-0.5 rounded-full">
                                                    Baru
                                                </span>
                                                <span
                                                    class="flex items-center gap-1 text-[11px] text-gray-400 dark:text-gray-500">
                                                    <i class="far fa-clock text-[10px]"></i>
                                                    <span x-text="n.relativeTime"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </template>
                        </div>

                        {{-- Footer --}}
                        <div class="notif-panel-footer px-5 py-3 border-t border-gray-100 dark:border-gray-700/50">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] text-gray-400 dark:text-gray-500">
                                    Menampilkan
                                    <span x-text="filteredNotifications.length"></span>
                                    dari
                                    <span x-text="notifications.length"></span>
                                    notifikasi
                                </span>
                                <span class="flex items-center gap-1 text-[11px] text-gray-400 dark:text-gray-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block animate-pulse"></span>
                                    Live
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-data="{ open: false }" class="relative">
                    <button id="profile-avatar" @click="open = !open"
                        class="flex items-center gap-2 p-2 rounded-full hover:bg-amber-50 dark:hover:bg-gray-700 transition">
                        <img src="{{ asset('images/profile/' . Auth::user()->profile_photo_path) }}"
                            class="w-8 h-8 lg:w-9 lg:h-9 rounded-full border"
                            onerror="this.src='{{ asset('images/avatar.png') }}'">
                        <span
                            class="hidden sm:block text-sm font-medium text-gray-700 dark:text-gray-200">{{ Auth::user()->name }}</span>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition
                        class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-xl shadow-lg py-2 z-50">
                        <a href="{{ route('admin.edit', Auth::user()->hashid) }}"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-white">
                            <i class="fas fa-user-cog w-5"></i> Pengaturan Akun
                        </a>
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                            <i class="fas fa-sign-out-alt w-5"></i> Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Sidebar hanya untuk admin/super --}}
        @if ($user->isAdminOrSuper())
            <aside class="sidebar" id="sidebar-menu"
                :class="{ 'open': sidebarOpen, 'collapsed': collapsed && window.innerWidth >= 1024 }">
                <nav class="p-4 space-y-1">
                    <a href="{{ route('admin.dashboard') }}"
                        class="menu-item flex items-center gap-4 px-4 py-3 text-base {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-gray-600 dark:text-gray-300' }} ripple">
                        <i class="fas fa-tachometer-alt w-6 h-6 text-center text-lg"></i>
                        <span class="sidebar-text">Dashboard</span>
                        <span class="collapsed-tooltip">Dashboard</span>
                    </a>
                    <a href="{{ route('alats.index') }}"
                        class="menu-item flex items-center gap-4 px-4 py-3 text-base {{ request()->routeIs('alats.index') ? 'active' : 'text-gray-600 dark:text-gray-300' }} ripple">
                        <i class="fas fa-tools w-6 h-6 text-center text-lg"></i>
                        <span class="sidebar-text">Data Alat</span>
                        <span class="collapsed-tooltip">Data Alat</span>
                    </a>
                    <a href="{{ route('kategoris.index') }}"
                        class="menu-item flex items-center gap-4 px-4 py-3 text-base {{ request()->routeIs('kategori.*') ? 'active' : 'text-gray-600 dark:text-gray-300' }} ripple">
                        <i class="fas fa-tags w-6 h-6 text-center text-lg"></i>
                        <span class="sidebar-text">Kategori Alat</span>
                        <span class="collapsed-tooltip">Kategori</span>
                    </a>
                    <a href="{{ route('kalibrasis.index') }}"
                        class="menu-item flex items-center gap-4 px-4 py-3 text-base {{ request()->routeIs('kalibrasi.*') ? 'active' : 'text-gray-600 dark:text-gray-300' }} ripple">
                        <i class="fas fa-wrench w-6 h-6 text-center text-lg"></i>
                        <span class="sidebar-text">Kalibrasi</span>
                        <span class="collapsed-tooltip">Kalibrasi</span>
                    </a>
                    <a href="{{ route('alats.daftarRiwayat') }}"
                        class="menu-item flex items-center gap-4 px-4 py-3 text-base text-gray-600 dark:text-gray-300 ripple">
                        <i class="fas fa-history w-6 h-6 text-center text-lg"></i>
                        <span class="sidebar-text">Riwayat Alat</span>
                        <span class="collapsed-tooltip">Riwayat Alat</span>
                    </a>
                    <a href="{{ route('pengambilan_alat.index') }}"
                        class="menu-item flex items-center gap-4 px-4 py-3 text-base {{ request()->routeIs('pengambilan_alat.*') ? 'active' : 'text-gray-600 dark:text-gray-300' }} ripple">
                        <i class="fas fa-hand-holding w-6 h-6 text-center text-lg"></i>
                        <span class="sidebar-text">Pengambilan Alat</span>
                        <span class="collapsed-tooltip">Pengambilan</span>
                    </a>
                    <a href="{{ route('pengembalian_alat.index') }}"
                        class="menu-item flex items-center gap-4 px-4 py-3 text-base {{ request()->routeIs('pengembalian_alat.*') ? 'active' : 'text-gray-600 dark:text-gray-300' }} ripple">
                        <i class="fas fa-undo-alt w-6 h-6 text-center text-lg"></i>
                        <span class="sidebar-text">Pengembalian Alat</span>
                        <span class="collapsed-tooltip">Pengembalian</span>
                    </a>
                    <div x-data="{ open: {{ request()->routeIs('bagian.*', 'admin.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                            class="menu-item w-full flex items-center justify-between px-4 py-3 text-base text-gray-600 dark:text-gray-300 ripple relative">
                            <div class="flex items-center gap-4">
                                <i class="fas fa-users w-6 h-6 text-center text-lg"></i>
                                <span class="sidebar-text">Manajemen</span>
                            </div>
                            <i class="fas fa-chevron-down chevron text-xs" :class="{ 'rotate-180': open }"></i>
                            <span class="collapsed-tooltip">Manajemen</span>
                        </button>
                        <div x-show="open" x-transition class="pl-12 mt-1 space-y-1 sidebar-text">
                            <a href="{{ route('bagian.index') }}"
                                class="flex items-center gap-4 px-4 py-2.5 rounded-lg text-base hover:bg-amber-50 dark:hover:bg-gray-700 {{ request()->routeIs('bagian.*') ? 'text-amber-700 dark:text-amber-300 font-medium bg-amber-50 dark:bg-gray-700' : '' }}">Bagian</a>
                            <a href="{{ route('admin.index') }}"
                                class="flex items-center gap-4 px-4 py-2.5 rounded-lg text-base hover:bg-amber-50 dark:hover:bg-gray-700 {{ request()->routeIs('admin.index') ? 'text-amber-700 dark:text-amber-300 font-medium bg-amber-50 dark:bg-gray-700' : '' }}">User</a>
                        </div>
                    </div>
                    <div class="border-t my-4 dark:border-gray-700"></div>
                    <a href="{{ route('admin.edit', Auth::user()->hashid ?? '') }}"
                        class="menu-item flex items-center gap-4 px-4 py-3 text-base text-gray-600 dark:text-gray-300 ripple">
                        <i class="fas fa-user-cog w-6 h-6 text-center text-lg"></i>
                        <span class="sidebar-text">Akun Saya</span>
                        <span class="collapsed-tooltip">Akun Saya</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="menu-item w-full flex items-center gap-4 px-4 py-3 text-base text-red-600 ripple">
                            <i class="fas fa-sign-out-alt w-6 h-6 text-center text-lg"></i>
                            <span class="sidebar-text">Logout</span>
                            <span class="collapsed-tooltip">Logout</span>
                        </button>
                    </form>
                </nav>
            </aside>
        @endif

        {{-- Main Content --}}
        <main
            class="main-content relative z-0 pt-26 lg:pt-28 p-6 lg:p-8 min-h-[calc(100vh-64px)] dark:bg-gray-900 transition-colors {{ $user->isAdminOrSuper() ? '' : '!ml-0' }}">

            {{-- ✨ Welcome Card untuk Karyawan di Dashboard ✨ --}}
            @unless ($user->isAdminOrSuper())
                @if (request()->routeIs('karyawan.dashboard'))
                    @php
                        $hour = now()->format('H');
                        $greeting = $hour < 11 ? 'Pagi' : ($hour < 15 ? 'Siang' : ($hour < 18 ? 'Sore' : 'Malam'));
                        $emoji = $hour < 11 ? '🌅' : ($hour < 15 ? '☀️' : ($hour < 18 ? '🌤️' : '🌙'));
                    @endphp
                    <div class="welcome-card rounded-2xl p-6 mb-6 animate-fade-in">
                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-1">
                                    Selamat {{ $greeting }}, {{ $user->name }}! {{ $emoji }}
                                </h2>
                                <p class="text-gray-600 dark:text-gray-300">
                                    Apa yang ingin Anda lakukan hari ini?
                                </p>
                            </div>
                            <div class="flex gap-3 flex-wrap">
                                <a href="{{ route('pengambilan_alat.create') }}"
                                    class="quick-action-btn inline-flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white rounded-xl font-medium shadow-lg">
                                    <i class="fas fa-plus"></i> Ambil Alat
                                </a>
                                <a href="{{ route('pengembalian_alat.index') }}"
                                    class="quick-action-btn inline-flex items-center gap-2 px-5 py-3 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl font-medium shadow-md border border-gray-200 dark:border-gray-700">
                                    <i class="fas fa-undo-alt"></i> Kembalikan
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endunless

            <div class="animate-fade-in dark:text-gray-100">
                @if (isset($breadcrumbs))
                    <nav class="flex mb-5" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm">
                            @foreach ($breadcrumbs as $breadcrumb)
                                <li class="inline-flex items-center animate-fade-in"
                                    style="animation-delay: {{ $loop->index * 0.1 }}s">
                                    @if (!$loop->last)
                                        <a href="{{ $breadcrumb['url'] }}"
                                            class="text-gray-500 dark:text-gray-400 hover:text-gold transition">
                                            {{ $breadcrumb['name'] }}
                                        </a>
                                        <i class="fas fa-chevron-right mx-2 text-xs text-gray-400 dark:text-gray-500"></i>
                                    @else
                                        <span class="text-gold font-medium">{{ $breadcrumb['name'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                @endif
                @yield('content')
            </div>
        </main>
    @else
        <main class="animate-fade-in dark:text-gray-100">
            @yield('content')
        </main>
    @endauth

    <button x-show="scrolled" @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="fixed bottom-6 right-6 z-50 p-3 bg-gold hover:bg-gold-light text-white rounded-full shadow-lg transition transform hover:scale-110"
        x-transition>
        <i class="fas fa-arrow-up"></i>
    </button>

    @livewireScripts

    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>

    @stack('scripts')

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('appLayout', () => ({
                sidebarOpen: false,
                collapsed: false,
                unreadCount: {{ auth()->check() ? auth()->user()->unreadNotifications->count() : 0 }},
                loading: true,
                scrolled: false,
                scrollProgress: 0,
                openSearch: false,
                toasts: [
                    @if (session('success'))
                        {
                            type: 'success',
                            message: @json(session('success'))
                        },
                    @endif
                    @if (session('error'))
                        {
                            type: 'error',
                            message: @json(session('error'))
                        },
                    @endif
                ],

                init() {
                    window.addEventListener('load', () => setTimeout(() => this.loading = false, 200));

                    this.$watch('sidebarOpen', val => {
                        if (val && window.innerWidth < 1024) {
                            document.querySelectorAll('.sidebar a').forEach(a => {
                                a.addEventListener('click', () => this.sidebarOpen =
                                    false);
                            });
                        }
                    });

                    window.addEventListener('scroll', () => {
                        const winScroll = document.documentElement.scrollTop;
                        const height = document.documentElement.scrollHeight - document
                            .documentElement.clientHeight;
                        this.scrollProgress = (winScroll / height) * 100;
                        this.scrolled = winScroll > 200;
                    });
                },

                toggleSidebar() {
                    this.sidebarOpen = !this.sidebarOpen;
                },

                handleKeydown(event) {
                    if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
                        event.preventDefault();
                        this.openSearch = true;
                    }
                    if (event.key === 'Escape' && this.openSearch) {
                        this.openSearch = false;
                    }
                },

                async markAllAsRead() {
                    try {
                        const res = await fetch('{{ route('notifications.markAllRead') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.unreadCount = 0;
                            const notifComponent = document.querySelector(
                                '[x-data*="notifications"]')._x_dataStack[0];
                            if (notifComponent) {
                                notifComponent.notifications = notifComponent.notifications.map(n =>
                                    ({
                                        ...n,
                                        read: true
                                    }));
                            }
                        }
                    } catch (e) {
                        console.error(e);
                    }
                }
            }));

            // ★ Notification panel component ★
            Alpine.data('notificationPanel', (initialData, csrfToken) => ({
                open: false,
                notifications: [],
                activeFilter: 'all',
                swipeOffset: {},
                swipeProgress: {},
                _touchStartX: {},
                _touchStartY: {},
                _tickInterval: null,
                shouldRing: false,

                init() {
                    this.notifications = (initialData || []).map(n => ({
                        ...n,
                        relativeTime: this._humanTime(n.createdAt || null, n.time),
                    }));

                    this.shouldRing = this.unreadCount > 0;
                    if (this.shouldRing) {
                        setInterval(() => {
                            if (!this.open && this.unreadCount > 0) {
                                this.shouldRing = false;
                                this.$nextTick(() => {
                                    this.shouldRing = true;
                                });
                            }
                        }, 8000);
                    }

                    this._startTick();

                    this.$watch('open', val => {
                        if (val) {
                            this._startTick();
                            this.$nextTick(() => {
                                const el = document.getElementById('notif-scroll-area');
                                if (el) el.scrollTop = 0;
                            });
                        } else {
                            this._stopTick();
                        }
                    });

                    window.addEventListener('keydown', e => {
                        if (e.key === 'Escape' && this.open) this.close();
                    });

                    window.addEventListener('notif:new', e => {
                        const n = e.detail;
                        if (!n) return;
                        this.notifications.unshift({
                            ...n,
                            relativeTime: 'baru saja',
                        });
                    });
                },

                get unreadCount() {
                    return this.notifications.filter(n => !n.read).length;
                },

                // ✨ Getter untuk notifikasi urgent
                get urgentCount() {
                    return this.notifications.filter(n => !n.read && n.priority === 'high').length;
                },

                get filteredNotifications() {
                    if (this.activeFilter === 'unread') {
                        return this.notifications.filter(n => !n.read);
                    }
                    if (this.activeFilter === 'urgent') {
                        return this.notifications.filter(n => !n.read && n.priority === 'high');
                    }
                    return this.notifications;
                },

                toggle() {
                    this.open ? this.close() : this.open = true;
                },

                close() {
                    this.open = false;
                },

                async markOneAsRead(n) {
                    if (n.read) return;
                    n.read = true;

                    try {
                        await fetch('/notifications/' + n.id + '/mark-read', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                        });
                    } catch (err) {
                        console.warn('mark-read error:', err);
                    }
                },

                async handleItemClick(n) {
                    await this.markOneAsRead(n);
                    if (n.url && n.url !== '#') {
                        window.location.href = n.url;
                    }
                    this.close();
                },

                async markAllAsRead() {
                    const hadUnread = this.unreadCount > 0;
                    this.notifications = this.notifications.map(n => ({
                        ...n,
                        read: true
                    }));

                    if (!hadUnread) return;

                    try {
                        const res = await fetch('{{ route('notifications.markAllRead') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                        });
                        const data = await res.json();
                        if (data.success) {
                            const area = document.getElementById('notif-scroll-area');
                            if (area) area.classList.add('notif-flash');
                            setTimeout(() => area?.classList.remove('notif-flash'), 600);
                        }
                    } catch (err) {
                        console.warn('markAllRead error:', err);
                    }
                },

                touchStart(event, idx) {
                    const t = event.touches[0];
                    this._touchStartX[idx] = t.clientX;
                    this._touchStartY[idx] = t.clientY;
                },

                touchMove(event, idx) {
                    if (!(idx in this._touchStartX)) return;
                    const dx = event.touches[0].clientX - this._touchStartX[idx];
                    const dy = Math.abs(event.touches[0].clientY - this._touchStartY[idx]);
                    if (dy > 12) {
                        this._resetSwipe(idx);
                        return;
                    }
                    if (dx < 0) {
                        this._resetSwipe(idx);
                        return;
                    }

                    const clamped = Math.min(dx, 120);
                    this.swipeOffset[idx] = clamped;
                    this.swipeProgress[idx] = Math.min(clamped / 80, 1);
                },

                touchEnd(event, idx) {
                    const offset = this.swipeOffset[idx] || 0;
                    if (offset > 75) {
                        this._dismissItem(idx);
                    } else {
                        this._resetSwipe(idx);
                    }
                },

                _dismissItem(idx) {
                    this.swipeOffset[idx] = 400;
                    setTimeout(() => {
                        const filtered = this.filteredNotifications;
                        if (!filtered[idx]) return;
                        const id = filtered[idx].id;
                        this.markOneAsRead(filtered[idx]);
                        this.notifications = this.notifications.filter(n => n.id !== id);
                        delete this.swipeOffset[idx];
                        delete this.swipeProgress[idx];
                    }, 250);
                },

                _resetSwipe(idx) {
                    this.swipeOffset[idx] = 0;
                    this.swipeProgress[idx] = 0;
                },

                _startTick() {
                    if (this._tickInterval) return;
                    this._tickInterval = setInterval(() => {
                        this.notifications = this.notifications.map(n => ({
                            ...n,
                            relativeTime: this._humanTime(n.createdAt, n.time),
                        }));
                    }, 30000);
                },

                _stopTick() {
                    if (this._tickInterval) {
                        clearInterval(this._tickInterval);
                        this._tickInterval = null;
                    }
                },

                _humanTime(isoString, fallback) {
                    if (!isoString) return fallback || '';
                    const diff = (Date.now() - new Date(isoString).getTime()) / 1000;
                    if (diff < 10) return 'baru saja';
                    if (diff < 60) return Math.floor(diff) + ' detik lalu';
                    if (diff < 3600) {
                        const m = Math.floor(diff / 60);
                        return m + ' menit lalu';
                    }
                    if (diff < 86400) {
                        const h = Math.floor(diff / 3600);
                        return h + ' jam lalu';
                    }
                    const d = Math.floor(diff / 86400);
                    if (d === 1) return 'kemarin';
                    if (d < 7) return d + ' hari lalu';
                    return new Date(isoString).toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'short',
                    });
                },
            }));
        });
    </script>

    <!-- Driver.js JS -->
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.0/dist/driver.js.iife.js"></script>
</body>

</html>
@include('components.onboarding')
