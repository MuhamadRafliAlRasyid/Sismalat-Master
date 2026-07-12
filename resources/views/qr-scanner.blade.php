@extends('layouts.app')

@section('title', 'Scan & Upload QR Code')

@section('content')
    <div class="max-w-lg mx-auto py-8 px-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-amber-100 p-6">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <i class="fas fa-qrcode text-amber-500"></i> Scan atau Upload QR Code Alat
            </h2>

            {{-- Container scanner kamera --}}
            <div id="reader" class="w-full mx-auto border-2 border-dashed border-amber-300 rounded-xl overflow-hidden"
                style="min-height: 300px;"></div>

            {{-- Preview gambar upload --}}
            <div id="preview-container" class="mt-4 hidden">
                <p class="text-sm text-gray-500 mb-1">Preview gambar yang diunggah:</p>
                <img id="preview-img" class="max-w-full h-auto border rounded-lg mx-auto" style="max-height: 200px;">
            </div>

            {{-- Hasil scan --}}
            <div id="result" class="mt-4 text-center">
                <p id="scan-message" class="text-gray-600 dark:text-gray-400 hidden"></p>
            </div>

            {{-- Tombol kontrol --}}
            <div class="mt-4 flex flex-wrap justify-center gap-3">
                <button id="start-scan" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition">
                    <i class="fas fa-camera mr-2"></i>Mulai Scan
                </button>
                <button id="stop-scan"
                    class="px-4 py-2 bg-gray-300 dark:bg-gray-700 hover:bg-gray-400 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-lg transition hidden">
                    <i class="fas fa-stop mr-2"></i>Berhenti
                </button>
                <button id="upload-btn" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition">
                    <i class="fas fa-upload mr-2"></i>Unggah QR
                </button>
                <input type="file" id="qr-file-input" accept="image/*" class="hidden" />
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Library untuk scan kamera -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <!-- Library jsQR untuk membaca gambar statis -->
    <script src="https://unpkg.com/jsqr@1.4.0/dist/jsQR.js"></script>

    <script>
        // DOM Elements
        const readerDiv = document.getElementById('reader');
        const startBtn = document.getElementById('start-scan');
        const stopBtn = document.getElementById('stop-scan');
        const uploadBtn = document.getElementById('upload-btn');
        const fileInput = document.getElementById('qr-file-input');
        const messageEl = document.getElementById('scan-message');
        const previewContainer = document.getElementById('preview-container');
        const previewImg = document.getElementById('preview-img');

        let html5QrCode;


        async function processQRCode(decodedText) {
            decodedText = decodedText.trim();
            console.log('Decoded text:', decodedText);

            let hashid = decodedText;
            if (decodedText.startsWith('http://') || decodedText.startsWith('https://')) {
                try {
                    const url = new URL(decodedText);
                    hashid = url.searchParams.get('alat_id') || decodedText;
                    console.log('Extracted hashid from URL:', hashid);
                } catch (e) {
                    console.warn('Gagal parsing URL, gunakan teks asli');
                }
            }

            messageEl.classList.remove('hidden');
            messageEl.textContent = 'Memverifikasi QR Code...';
            messageEl.classList.remove('text-red-500', 'text-yellow-500');
            messageEl.classList.add('text-blue-500');

            try {
                const response = await fetch('{{ route('qr-scanner.process') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        hashid: hashid
                    })
                });

                // Cek apakah respon berformat JSON
                const isJson = response.headers.get('content-type')?.includes('application/json');

                if (!response.ok && !isJson) {
                    // Jika crash berupa HTML (Error 500 berat)
                    const errorHtml = await response.text();
                    console.error('--- LARAVEL CRASH DETECTED ---', errorHtml);
                    throw new Error('Server status error: ' + response.status);
                }

                // Baca data JSON (baik status 200 maupun status 404 dari controller)
                const data = await response.json();

                if (response.ok && data.success && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    // Menampilkan pesan "QR Code tidak valid (hashid tidak dikenal)" langsung di layar
                    messageEl.textContent = data.message || 'QR Code tidak dikenali.';
                    messageEl.classList.add('text-red-500');
                    messageEl.classList.remove('text-blue-500');

                    if (startBtn.classList.contains('hidden')) {
                        startBtn.classList.remove('hidden');
                        stopBtn.classList.add('hidden');
                    }
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                messageEl.textContent = 'Terjadi kesalahan sistem. Silakan coba lagi.';
                messageEl.classList.add('text-red-500');
                messageEl.classList.remove('text-blue-500');
            }
        }

        // ---- Scan dari kamera (html5-qrcode) ----
        function onScanSuccess(decodedText, decodedResult) {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    console.log('Scanner stopped');
                }).catch(err => console.error(err));
            }
            processQRCode(decodedText);
        }

        function onScanFailure(error) {
            // Abaikan error biasa
        }

        startBtn.addEventListener('click', () => {
            html5QrCode = new Html5Qrcode("reader");
            html5QrCode.start({
                    facingMode: "environment"
                }, {
                    fps: 10,
                    qrbox: {
                        width: 250,
                        height: 250
                    }
                },
                onScanSuccess,
                onScanFailure
            ).then(() => {
                startBtn.classList.add('hidden');
                stopBtn.classList.remove('hidden');
                messageEl.classList.add('hidden');
                previewContainer.classList.add('hidden');
            }).catch(err => {
                console.error('Gagal memulai scanner:', err);
                alert('Tidak dapat mengakses kamera. Pastikan Anda memberikan izin.');
            });
        });

        stopBtn.addEventListener('click', () => {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    startBtn.classList.remove('hidden');
                    stopBtn.classList.add('hidden');
                    messageEl.classList.add('hidden');
                }).catch(err => console.error(err));
            }
        });

        // ========== UPLOAD GAMBAR dengan jsQR ==========
        uploadBtn.addEventListener('click', () => {
            fileInput.click();
        });

        // Fungsi menambah quiet zone (border putih)
        function addQuietZoneToImage(imageElement, padding = 40) {
            return new Promise((resolve) => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const imgWidth = imageElement.width;
                const imgHeight = imageElement.height;
                const finalPadding = Math.max(padding, Math.floor(imgWidth * 0.1));
                canvas.width = imgWidth + (finalPadding * 2);
                canvas.height = imgHeight + (finalPadding * 2);
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(imageElement, finalPadding, finalPadding, imgWidth, imgHeight);
                canvas.toBlob(blob => {
                    resolve(URL.createObjectURL(blob));
                }, 'image/png');
            });
        }

        // Fungsi utama membaca QR dari file
        async function readQRFromFile(file) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.src = URL.createObjectURL(file);
                img.onload = async () => {
                    previewImg.src = img.src;
                    previewContainer.classList.remove('hidden');

                    // Scan langsung
                    let canvas = document.createElement('canvas');
                    let ctx = canvas.getContext('2d');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    ctx.drawImage(img, 0, 0);
                    let imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    let qrResult = jsQR(imageData.data, canvas.width, canvas.height, {
                        inversionAttempts: "dontInvert",
                    });

                    if (qrResult) {
                        resolve(qrResult.data);
                        return;
                    }

                    // Coba dengan quiet zone
                    console.log('Direct scan failed, adding quiet zone...');
                    try {
                        const paddedUrl = await addQuietZoneToImage(img, 80);
                        const paddedImg = new Image();
                        paddedImg.src = paddedUrl;
                        paddedImg.onload = () => {
                            canvas = document.createElement('canvas');
                            ctx = canvas.getContext('2d');
                            canvas.width = paddedImg.width;
                            canvas.height = paddedImg.height;
                            ctx.drawImage(paddedImg, 0, 0);
                            imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                            qrResult = jsQR(imageData.data, canvas.width, canvas.height, {
                                inversionAttempts: "dontInvert",
                            });
                            URL.revokeObjectURL(paddedUrl);
                            if (qrResult) {
                                resolve(qrResult.data);
                            } else {
                                reject(new Error('Tidak terdeteksi setelah penambahan quiet zone'));
                            }
                        };
                        paddedImg.onerror = () => reject(new Error(
                            'Gagal memuat gambar dengan quiet zone'));
                    } catch (err) {
                        reject(err);
                    }
                };
                img.onerror = () => reject(new Error('Gagal memuat gambar'));
            });
        }

        fileInput.addEventListener('change', async (event) => {
            const file = event.target.files[0];
            if (!file) return;

            // Hentikan kamera jika sedang berjalan
            if (html5QrCode && html5QrCode.isScanning) {
                try {
                    await html5QrCode.stop();
                    startBtn.classList.remove('hidden');
                    stopBtn.classList.add('hidden');
                } catch (e) {
                    console.warn(e);
                }
            }

            messageEl.classList.remove('hidden');
            messageEl.textContent = 'Memproses gambar...';
            messageEl.classList.remove('text-red-500', 'text-green-500');
            messageEl.classList.add('text-yellow-500');
            previewContainer.classList.remove('hidden');

            try {
                const decodedText = await readQRFromFile(file);
                await processQRCode(decodedText);
            } catch (err) {
                console.error(err);
                messageEl.textContent = 'Gambar tidak mengandung QR Code yang valid.';
                messageEl.classList.add('text-red-500');
                messageEl.classList.remove('text-yellow-500');
            } finally {
                fileInput.value = '';
            }
        });
    </script>
@endpush
