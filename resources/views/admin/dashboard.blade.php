@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div
        class="min-h-screen bg-gradient-to-br from-slate-50 via-amber-50/20 to-orange-50/30 p-4 md:p-6 flex flex-col items-center relative overflow-hidden">

        {{-- Orbs dekoratif --}}
        <div class="absolute top-0 left-0 w-96 h-96 bg-amber-200/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-orange-200/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-6xl relative z-10" x-data="dashboardAdmin()">

            {{-- Header --}}
            <div class="text-center mb-8 animate-fade-in">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 dark:text-white inline-flex items-center gap-3">
                    <i class="fas fa-tachometer-alt text-amber-500 animate-bounce-slow"></i>
                    Dashboard
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">Selamat datang, <span
                        class="font-semibold text-amber-600">{{ Auth::user()->name }}</span></p>
                <div class="mt-2 text-sm text-gray-400 flex items-center justify-center gap-2">
                    <i class="fas fa-calendar-alt"></i> <span
                        x-text="new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })"></span>
                </div>
            </div>

            {{-- Quick Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                @php
                    $cards = [
                        [
                            'label' => 'Total Alat',
                            'value' => $totalAlat ?? 0,
                            'icon' => 'fa-boxes',
                            'color' => 'amber',
                            'bg' => 'from-amber-400 to-amber-500',
                            'id' => 'total',
                        ],
                        [
                            'label' => 'Dipinjam',
                            'value' => $totalDipinjam ?? 0,
                            'icon' => 'fa-hand-holding',
                            'color' => 'orange',
                            'bg' => 'from-orange-400 to-orange-500',
                            'id' => 'dipinjam',
                        ],
                        [
                            'label' => 'Dikembalikan',
                            'value' => $totalDikembalikan ?? 0,
                            'icon' => 'fa-undo-alt',
                            'color' => 'yellow',
                            'bg' => 'from-yellow-400 to-yellow-500',
                            'id' => 'dikembalikan',
                        ],
                    ];
                @endphp

                @foreach ($cards as $card)
                    <div class="group cursor-pointer" @click="selectCard('{{ $card['id'] }}')"
                        :class="{ 'ring-2 ring-amber-400 scale-105 shadow-xl': activeCard === '{{ $card['id'] }}' }">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 h-full transition-all duration-300 group-hover:shadow-xl group-hover:border-amber-200 transform group-hover:-translate-y-1">
                            <div class="flex items-center justify-between mb-4">
                                <div
                                    class="w-14 h-14 bg-gradient-to-br {{ $card['bg'] }} rounded-xl flex items-center justify-center text-white shadow-md">
                                    <i class="fas {{ $card['icon'] }} text-xl"></i>
                                </div>
                                <span
                                    class="text-xs font-medium text-gray-400 bg-gray-50 dark:bg-gray-900 px-3 py-1 rounded-full">
                                    {{ $card['label'] }}
                                </span>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mb-1">{{ $card['label'] }}</p>
                            <p class="text-3xl font-bold text-gray-800 dark:text-white counter"
                                x-text="formatNumber({{ $card['value'] }})" x-init="animateCounter($el, {{ $card['value'] }})">0</p>
                            <div
                                class="mt-2 text-xs text-amber-500 opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                                <i class="fas fa-mouse-pointer"></i> Klik untuk sorot grafik
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Return Ratio Bar --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 mb-8 animate-slide-up">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-100 mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-line text-amber-500"></i> Rasio Pengembalian
                </h3>
                @php
                    $rasio = ($totalDipinjam ?? 0) > 0 ? round(($totalDikembalikan / $totalDipinjam) * 100) : 0;
                @endphp
                <div class="flex items-center gap-4">
                    <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-5 overflow-hidden shadow-inner">
                        <div class="bg-gradient-to-r from-amber-400 to-orange-500 h-full rounded-full transition-all duration-1000 ease-out"
                            style="width: 0%" x-init="setTimeout(() => $el.style.width = '{{ $rasio }}%', 200)">
                        </div>
                    </div>
                    <span class="text-lg font-bold text-amber-600 w-16 text-right">{{ $rasio }}%</span>
                </div>
                <p class="text-xs text-gray-400 mt-2">
                    {{ $totalDikembalikan }} dari {{ $totalDipinjam }} barang dipinjam telah kembali
                </p>
            </div>

            {{-- Chart Section --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 animate-slide-up">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-2">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-100 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-amber-500"></i> Statistik Alat & Transaksi
                    </h3>
                    <div x-show="activeCard"
                        class="text-xs font-medium bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 px-3 py-1 rounded-full flex items-center gap-1">
                        <i class="fas fa-search"></i> Sorot: <span x-text="activeCardLabel"></span>
                    </div>
                </div>
                <div
                    class="bg-amber-50/20 dark:bg-gray-900/30 p-4 rounded-2xl border border-amber-100 dark:border-gray-700">
                    <canvas id="dashboardChart" height="280"></canvas>
                </div>
            </div>

            {{-- Toast Notification --}}
            <div x-show="toast" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4"
                class="fixed bottom-5 right-5 bg-white dark:bg-gray-800 shadow-2xl border border-amber-200 dark:border-gray-700 px-5 py-3 rounded-2xl z-50 flex items-center gap-3"
                x-init="setTimeout(() => toast = false, 3000)">
                <i class="fas fa-check-circle text-amber-500 text-xl"></i>
                <span x-text="toastMessage" class="text-gray-700 dark:text-white font-medium"></span>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        function dashboardAdmin() {
            return {
                activeCard: '',
                toast: false,
                toastMessage: '',
                chart: null,

                init() {
                    this.initChart();
                },

                selectCard(id) {
                    this.activeCard = id;
                    const labels = {
                        'total': 'Total Alat',
                        'dipinjam': 'Barang Dipinjam',
                        'dikembalikan': 'Barang Dikembalikan'
                    };
                    this.toastMessage = `Data ${labels[id]} disorot`;
                    this.toast = true;
                    this.highlightBar(id);
                },

                get activeCardLabel() {
                    const labels = {
                        'total': 'Total Alat',
                        'dipinjam': 'Dipinjam',
                        'dikembalikan': 'Dikembalikan'
                    };
                    return this.activeCard ? labels[this.activeCard] : '';
                },

                formatNumber(num) {
                    return new Intl.NumberFormat('id-ID').format(num);
                },

                animateCounter(el, target) {
                    const duration = 1500;
                    const start = 0;
                    const range = target - start;
                    const increment = range / (duration / 16);
                    let current = start;
                    const timer = setInterval(() => {
                        current += increment;
                        if ((increment > 0 && current >= target) || (increment < 0 && current <= target)) {
                            el.innerText = this.formatNumber(target);
                            clearInterval(timer);
                        } else {
                            el.innerText = this.formatNumber(Math.floor(current));
                        }
                    }, 16);
                },

                initChart() {
                    const canvas = document.getElementById('dashboardChart');
                    if (!canvas) return;

                    const ctx = canvas.getContext('2d');

                    // Deteksi dark mode
                    const isDark = document.documentElement.classList.contains('dark');
                    const tickColor = isDark ? '#e2e8f0' : '#6b7280'; // gray-200 : gray-500
                    const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.04)';

                    const data = {
                        labels: ['Total Alat', 'Dipinjam', 'Dikembalikan'],
                        datasets: [{
                            label: 'Jumlah',
                            data: [
                                {{ $totalAlat ?? 0 }},
                                {{ $totalDipinjam ?? 0 }},
                                {{ $totalDikembalikan ?? 0 }}
                            ],
                            backgroundColor: [
                                'rgba(245, 158, 11, 0.7)',
                                'rgba(249, 115, 22, 0.7)',
                                'rgba(234, 179, 8, 0.7)'
                            ],
                            borderColor: [
                                '#f59e0b',
                                '#f97316',
                                '#eab308'
                            ],
                            borderWidth: 2,
                            borderRadius: 10,
                            borderSkipped: false,
                            hoverBackgroundColor: [
                                'rgba(245, 158, 11, 0.9)',
                                'rgba(249, 115, 22, 0.9)',
                                'rgba(234, 179, 8, 0.9)'
                            ]
                        }]
                    };

                    this.chart = new Chart(ctx, {
                        type: 'bar',
                        data: data,
                        options: {
                            responsive: true,
                            animation: {
                                duration: 1500,
                                easing: 'easeOutQuart',
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: isDark ? '#334155' : '#fff',
                                    titleColor: isDark ? '#f1f5f9' : '#1f2937',
                                    bodyColor: isDark ? '#e2e8f0' : '#1f2937',
                                    borderColor: '#fbbf24',
                                    borderWidth: 1,
                                    displayColors: false,
                                    bodyFont: {
                                        weight: 'bold'
                                    },
                                    padding: 10,
                                    cornerRadius: 8,
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: gridColor
                                    },
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0,
                                        color: tickColor,
                                        font: {
                                            size: 12
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        color: tickColor,
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        }
                                    }
                                }
                            },
                            onClick: (event, elements, chart) => {
                                if (elements.length > 0) {
                                    const index = elements[0].index;
                                    const ids = ['total', 'dipinjam', 'dikembalikan'];
                                    this.selectCard(ids[index]);
                                }
                            }
                        }
                    });
                },

                highlightBar(id) {
                    if (!this.chart) return;

                    const map = {
                        'total': 0,
                        'dipinjam': 1,
                        'dikembalikan': 2
                    };
                    const index = map[id];

                    const bg = ['rgba(245, 158, 11, 0.7)', 'rgba(249, 115, 22, 0.7)', 'rgba(234, 179, 8, 0.7)'];
                    const border = ['#f59e0b', '#f97316', '#eab308'];
                    this.chart.data.datasets[0].backgroundColor = bg;
                    this.chart.data.datasets[0].borderColor = border;
                    this.chart.data.datasets[0].borderWidth = [2, 2, 2];

                    this.chart.data.datasets[0].backgroundColor[index] = 'rgba(245, 158, 11, 0.95)';
                    this.chart.data.datasets[0].borderColor[index] = '#92400e';
                    this.chart.data.datasets[0].borderWidth[index] = 4;

                    this.chart.update();
                }
            };
        }
    </script>

    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce-slow {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-6px);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.6s ease-out;
        }

        .animate-slide-up {
            animation: slide-up 0.8s ease-out;
        }

        .animate-bounce-slow {
            animation: bounce-slow 2.5s infinite;
        }
    </style>
@endpush
