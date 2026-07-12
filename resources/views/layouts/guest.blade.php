<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="{ darkMode: $persist(false).as('darkMode') }"
    :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title') - Tanaoroshi</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="{{ asset('images/logos.jpg') }}" />
    <link
        href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700&family=Space+Grotesk:wght@400;500;600&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    @livewireStyles
    @stack('styles')

    <style>
        :root {
            --gold-light: #f5c842;
            --gold: #e6a817;
            --gold-dark: #c88a00;
            --primary: #f59e0b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Sora', sans-serif;
            background: linear-gradient(135deg, #fefce8, #fff5eb, #facc15, #fef08a);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
            color: #1e293b;
        }

        .dark body {
            background: linear-gradient(135deg, #0f172a, #1e293b, #334155, #1e293b);
        }

        /* Background ambient elements */
        .bg-ambient {
            position: relative;
            min-height: 100vh;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
            z-index: 0;
            opacity: 0.35;
        }

        .orb1 {
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.6), transparent);
            top: -150px;
            left: -100px;
            animation: drift1 12s ease-in-out infinite alternate;
        }

        .orb2 {
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(254, 215, 170, 0.5), transparent);
            bottom: -120px;
            right: -80px;
            animation: drift2 14s ease-in-out infinite alternate;
        }

        .orb3 {
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(253, 230, 138, 0.6), transparent);
            top: 40%;
            right: 5%;
            animation: drift3 10s ease-in-out infinite alternate;
        }

        .orb4 {
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.4), transparent);
            bottom: 20%;
            left: 8%;
            animation: drift4 16s ease-in-out infinite alternate;
        }

        @keyframes drift1 {
            0% {
                transform: translate(0, 0) scale(1);
            }

            100% {
                transform: translate(40px, 25px) scale(1.1);
            }
        }

        @keyframes drift2 {
            0% {
                transform: translate(0, 0) scale(1);
            }

            100% {
                transform: translate(-30px, -20px) scale(1.08);
            }
        }

        @keyframes drift3 {
            0% {
                transform: translate(0, 0) scale(1);
            }

            100% {
                transform: translate(15px, 20px) scale(1.2);
            }
        }

        @keyframes drift4 {
            0% {
                transform: translate(0, 0) scale(1);
            }

            100% {
                transform: translate(-20px, -25px) scale(1.15);
            }
        }

        .grid-bg {
            position: absolute;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(230, 168, 23, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(230, 168, 23, 0.05) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: 0;
        }

        .dark .grid-bg {
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }

        .container {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
            max-width: 520px;
            margin: 0 auto;
        }

        .glass-card {
            width: 100%;
            background: white;
            border-radius: 32px;
            border: 1px solid rgba(230, 168, 23, 0.25);
            padding: 2.5rem 2.2rem 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02), 0 20px 60px rgba(230, 168, 23, 0.12);
            animation: slideUp 0.7s cubic-bezier(0.22, 1, 0.36, 1) forwards;
            position: relative;
            overflow: hidden;
        }

        .dark .glass-card {
            background: #1e293b;
            border-color: rgba(245, 200, 66, 0.15);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2), 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: -1px;
            left: 20%;
            right: 20%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold-dark), var(--gold-light), var(--gold), transparent);
            border-radius: 999px;
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes slideUp {
            0% {
                opacity: 0;
                transform: translateY(40px) scale(0.96);
            }

            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes shimmer {

            0%,
            100% {
                opacity: 0.4;
                left: 20%;
                right: 20%;
            }

            50% {
                opacity: 0.9;
                left: 10%;
                right: 10%;
            }
        }

        .text-gradient-gold {
            background: linear-gradient(135deg, #b45309, #d97706, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .inp {
            width: 100%;
            padding: 0.85rem 1rem 0.85rem 2.8rem;
            background: #fefce8;
            border: 1px solid rgba(230, 168, 23, 0.3);
            border-radius: 14px;
            color: #1e293b;
            font-size: 14px;
            font-family: 'Space Grotesk', sans-serif;
            outline: none;
            transition: all 0.3s ease;
        }

        .dark .inp {
            background: #334155;
            border-color: #475569;
            color: #e2e8f0;
        }

        .inp::placeholder {
            color: #9ca3af;
        }

        .inp:focus {
            border-color: #f59e0b;
            background: white;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.08);
        }

        .dark .inp:focus {
            background: #1e293b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15);
        }

        .field-wrap {
            position: relative;
        }

        .field-wrap i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: #9ca3af;
            transition: color 0.3s;
            pointer-events: none;
        }

        .field-wrap:focus-within i {
            color: #f59e0b;
        }

        .btn-primary,
        .submit {
            width: 100%;
            padding: 0.95rem;
            border-radius: 14px;
            border: none;
            cursor: pointer;
            background: linear-gradient(135deg, #e6a817, #f5c842, #facc15, #e6a817);
            background-size: 250% 250%;
            color: #0f172a;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Sora', sans-serif;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(230, 168, 23, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover,
        .submit:hover {
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 10px 28px rgba(230, 168, 23, 0.5);
            background-position: 100% 0;
        }

        .btn-primary:active {
            transform: translateY(0) scale(0.99);
        }

        .google-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0.85rem 1rem;
            border-radius: 14px;
            cursor: pointer;
            background: white;
            border: 1px solid rgba(230, 168, 23, 0.3);
            color: #334155;
            font-size: 14px;
            font-weight: 500;
            font-family: 'Space Grotesk', sans-serif;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .dark .google-btn {
            background: #1e293b;
            border-color: #475569;
            color: #e2e8f0;
        }

        .google-btn:hover {
            border-color: #f59e0b;
            background: #fffbeb;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(230, 168, 23, 0.15);
        }

        .dark .google-btn:hover {
            background: #334155;
        }

        .error-box {
            background: #fef2f2;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid #fecaca;
            margin-bottom: 20px;
            color: #991b1b;
            font-size: 13px;
            animation: shake 0.5s ease-in-out;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .dark .error-box {
            background: #7f1d1d20;
            border-color: #7f1d1d;
            color: #fecaca;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            20% {
                transform: translateX(-6px);
            }

            40% {
                transform: translateX(6px);
            }

            60% {
                transform: translateX(-4px);
            }

            80% {
                transform: translateX(4px);
            }
        }

        @media (max-width: 640px) {
            .glass-card {
                padding: 1.8rem 1.4rem 1.5rem;
                border-radius: 24px;
            }

            .container {
                padding: 16px;
            }

            .orb1 {
                width: 250px;
                height: 250px;
            }

            .orb2 {
                width: 200px;
                height: 200px;
            }
        }

        /* Dark mode text */
        .dark .text-gray-800 {
            color: #e2e8f0 !important;
        }

        .dark .text-gray-700 {
            color: #cbd5e1 !important;
        }

        .dark .text-gray-600 {
            color: #94a3b8 !important;
        }

        .dark .text-gray-500 {
            color: #9ca3af !important;
        }

        .dark .bg-white {
            background-color: #1e293b !important;
        }

        .dark .bg-gray-50 {
            background-color: #0f172a !important;
        }

        .dark .border-gray-200 {
            border-color: #334155 !important;
        }

        .dark .border-amber-200\/60 {
            border-color: rgba(245, 200, 66, 0.2) !important;
        }
    </style>
</head>

<body class="bg-ambient" x-data="guestLayout()" x-init="init()">
    <!-- Tombol dark mode toggle -->
    <div class="fixed top-4 right-4 z-50">
        <button @click="darkMode = !darkMode"
            class="p-2 rounded-full bg-white dark:bg-gray-800 border border-amber-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-amber-50 dark:hover:bg-gray-700 transition shadow-sm">
            <i class="fas" :class="darkMode ? 'fa-sun' : 'fa-moon'"></i>
        </button>
    </div>

    <div x-show="loading" x-transition.opacity.duration.500ms
        class="fixed inset-0 bg-white dark:bg-gray-800 z-[9999] flex items-center justify-center">
        <div class="w-12 h-12 border-4 border-amber-200 border-t-amber-500 rounded-full animate-spin"></div>
    </div>

    <div class="orb orb1"></div>
    <div class="orb orb2"></div>
    <div class="orb orb3"></div>
    <div class="orb orb4"></div>
    <div class="grid-bg"></div>
    <div class="particles" id="particles"></div>

    <div class="container">
        @yield('content')
    </div>

    <div x-show="toast.show" x-transition
        class="fixed top-5 right-5 z-[9999] bg-white dark:bg-gray-800 border border-amber-200 text-gray-800 dark:text-gray-100 px-5 py-3 rounded-xl shadow-lg max-w-sm"
        :class="toast.type === 'error' ? 'border-red-200 text-red-800 dark:text-red-300' : ''">
        <span x-text="toast.message"></span>
    </div>

    @livewireScripts
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')

    <script>
        function guestLayout() {
            return {
                loading: true,
                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },
                init() {
                    window.addEventListener('load', () => setTimeout(() => this.loading = false, 300));
                    setTimeout(() => this.loading = false, 3000);
                    this.initParticles();
                    this.initRippleEffects();
                },
                showToast(message, type = 'success') {
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.show = true;
                    setTimeout(() => this.toast.show = false, 3500);
                },
                initParticles() {
                    const pc = document.getElementById('particles');
                    if (!pc) return;
                    const cols = ['rgba(245,200,66,0.6)', 'rgba(252,211,77,0.5)', 'rgba(254,215,170,0.5)',
                        'rgba(255,255,255,0.3)'
                    ];
                    for (let i = 0; i < 30; i++) {
                        const p = document.createElement('div');
                        p.className = 'particle';
                        const size = Math.random() * 4 + 2;
                        p.style.cssText =
                            `width:${size}px;height:${size}px;left:${Math.random()*100}%;bottom:${Math.random()*15}%;background:${cols[Math.floor(Math.random()*cols.length)]};animation-duration:${Math.random()*10+8}s;animation-delay:${Math.random()*8}s;`;
                        pc.appendChild(p);
                    }
                },
                initRippleEffects() {
                    document.addEventListener('click', (e) => {
                        const btn = e.target.closest('.btn-primary,.submit');
                        if (!btn) return;
                        const ripple = document.createElement('span');
                        ripple.className = 'ripple-effect';
                        const rect = btn.getBoundingClientRect();
                        const size = Math.max(rect.width, rect.height);
                        ripple.style.cssText =
                            `position:absolute;border-radius:50%;background:rgba(255,255,255,0.4);width:${size}px;height:${size}px;left:${e.clientX-rect.left-size/2}px;top:${e.clientY-rect.top-size/2}px;transform:scale(0);animation:ripple 0.6s linear;`;
                        let container = btn.querySelector('.ripple-container');
                        if (!container) {
                            container = document.createElement('span');
                            container.className = 'ripple-container';
                            container.style.cssText =
                                'position:absolute;inset:0;overflow:hidden;border-radius:14px;pointer-events:none;';
                            btn.appendChild(container);
                        }
                        container.appendChild(ripple);
                        ripple.addEventListener('animationend', () => ripple.remove());
                    });
                }
            }
        }
    </script>
</body>

</html>
