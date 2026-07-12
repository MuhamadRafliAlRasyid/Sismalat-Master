@if (!auth()->user()->onboarding_completed)
    <div x-data="onboarding()" x-init="start()"></div>
    <script>
        function onboarding() {
            return {
                driver: null,
                start() {
                    this.driver = window.driver.js.driver({
                        showProgress: true,
                        animate: true,
                        smoothScroll: true,
                        steps: [{
                                element: '#sidebar-menu',
                                popover: {
                                    title: '📌 Menu Navigasi Utama',
                                    description: 'Pusat kendali SISMALAT. Anda dapat mengakses Data Alat, Kalibrasi, Pengambilan, Pengembalian, Riwayat Alat, dan Manajemen User.',
                                    side: 'right',
                                    align: 'start'
                                }
                            },
                            {
                                element: '#stat-cards',
                                popover: {
                                    title: '📊 Ringkasan Inventaris',
                                    description: 'Empat kartu ini menampilkan jumlah alat yang dipinjam, dikembalikan, dan total alat. Sangat membantu untuk monitoring cepat.',
                                    side: 'top',
                                    align: 'center'
                                }
                            },
                            {
                                element: '#alat-dipinjam-section',
                                popover: {
                                    title: '⏳ Alat Sedang Dipinjam',
                                    description: 'Bagian ini menunjukkan alat yang masih dipinjam. Anda bisa langsung memproses pengembalian dengan klik tombol "Kembalikan".',
                                    side: 'top',
                                    align: 'center'
                                }
                            },
                            {
                                element: '#aktivitas-terbaru-section',
                                popover: {
                                    title: '🕒 Riwayat Aktivitas Terbaru',
                                    description: 'Di sini tercatat log peminjaman terakhir. Berguna untuk melacak siapa yang mengambil alat dan kapan.',
                                    side: 'top',
                                    align: 'center'
                                }
                            },
                            {
                                element: '#notifications-bell',
                                popover: {
                                    title: '🔔 Pusat Notifikasi',
                                    description: 'Klik ikon lonceng di kanan atas untuk melihat notifikasi. Akan muncul pemberitahuan jika ada alat yang masa kalibrasinya hampir habis atau sudah expired.',
                                    side: 'bottom',
                                    align: 'center'
                                }
                            },
                            {
                                element: '#profile-avatar',
                                popover: {
                                    title: '👤 Profil & Akun',
                                    description: 'Klik avatar Anda di pojok kanan atas untuk mengedit profil, pengaturan akun, atau keluar dari sistem.',
                                    side: 'bottom',
                                    align: 'center'
                                }
                            },
                            {
                                element: '#search-button',
                                popover: {
                                    title: '🔍 Pencarian Cepat',
                                    description: 'Gunakan tombol ini (atau tekan Ctrl+K) untuk mencari halaman atau data secara instan. Sangat praktis!',
                                    side: 'bottom',
                                    align: 'center'
                                }
                            },
                            {
                                element: '#scan-qr-button',
                                popover: {
                                    title: '📷 Scan QR Code',
                                    description: 'Tombol di kanan bawah ini untuk memindai QR Code alat. Proses peminjaman atau pengembalian jadi lebih cepat!',
                                    side: 'left',
                                    align: 'center'
                                }
                            },
                            {
                                popover: {
                                    title: '🚀 Siap Bekerja!',
                                    description: 'Anda sekarang sudah mengenal antarmuka SISMALAT. Selamat mengelola inventaris dengan lebih efisien!',
                                    side: 'center',
                                    align: 'center'
                                }
                            }
                        ],
                        onDestroyed: () => {
                            this.complete();
                        }
                    });
                    this.driver.drive();
                },
                async complete() {
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').content;
                        const response = await fetch('{{ route('onboarding.complete') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        });
                        if (!response.ok) {
                            const errorText = await response.text();
                            throw new Error(`HTTP ${response.status}: ${errorText}`);
                        }
                        const result = await response.json();
                        console.log('Onboarding selesai:', result);
                    } catch (error) {
                        console.error('Gagal menyelesaikan onboarding:', error);
                        // Fallback: jika fetch gagal, set localStorage agar onboarding tidak muncul terus-menerus
                        localStorage.setItem('onboarding_attempted', '1');
                    }
                }
            }
        }
    </script>
@endif
