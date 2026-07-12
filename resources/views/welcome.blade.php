<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Welcome - Tanaoroshi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo.jpg') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: url('{{ asset('images/bg.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            color: #2c3e50;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #logo {
            opacity: 0;
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

        .animate-logo {
            opacity: 1;
            transform: rotate(360deg) scale(1.2);
        }
    </style>
</head>

<body>
    <div class="flex flex-col items-center">
        <img id="logo" alt="Abstract logo with blue and orange geometric shapes forming a square"
            class="w-32 h-32 mb-8" src="{{ asset('images/logo.jpg') }}" />
    </div>

    <script>
        // Animate logo on page load
        window.addEventListener("DOMContentLoaded", () => {
            const logo = document.getElementById("logo");

            // Tambahkan animasi setelah 0.5 detik untuk efek muncul
            setTimeout(() => {
                logo.classList.add("animate-logo");
            }, 5000);

            // Redirect ke halaman login setelah animasi selesai (0.5s + 0.3s delay)
            setTimeout(() => {
                window.location.href = "{{ route('login') }}";
            }, 800); // 0.5s animasi + 0.3s delay tambahan
        });
    </script>
</body>

</html>
