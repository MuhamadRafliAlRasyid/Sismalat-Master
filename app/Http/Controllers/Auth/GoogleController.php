<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleController extends Controller
{
    /**
     * ✅ Redirect ke Google
     */
    public function redirects()
    {
        try {
            Log::info('🔵 [Google] Redirecting to Google...');

            return Socialite::driver('google')
                ->with(['prompt' => 'select_account'])
                ->redirect();

        } catch (\Exception $e) {
            Log::error('❌ [Google] Redirect Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect('/login')->with('error', 'Gagal redirect ke Google: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Handle callback dari Google
     */
    public function callback()
    {
        try {
            Log::info('🟢 [Google] Callback received');
            Log::info('🟢 [Google] Request data:', $request = request()->all());

            // ✅ Cek apakah ada error dari Google
            if (request()->has('error')) {
                $error = request()->get('error');
                Log::error('❌ [Google] Error from Google:', ['error' => $error]);
                return redirect('/login')->with('error', 'Google error: ' . $error);
            }

            // ✅ Cek apakah ada code
            if (!request()->has('code')) {
                Log::error('❌ [Google] No code received');
                return redirect('/login')->with('error', 'Authorization code tidak diterima');
            }

            Log::info('🟢 [Google] Code received, fetching user...');

            // ✅ Ambil data user dari Google
            $googleUser = Socialite::driver('google')->stateless()->user();

            Log::info('✅ [Google] User data received:', [
                'id' => $googleUser->getId(),
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
            ]);

            // ✅ Validasi email
            if (!$googleUser->getEmail()) {
                Log::error('❌ [Google] Email not available');
                return redirect('/login')->with('error', 'Email Google tidak tersedia');
            }

            // ✅ Cek apakah user sudah ada
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                Log::info('🟡 [Google] User not found, creating new user...');

                // ✅ Buat user baru
                $user = User::create([
                    'name' => $googleUser->getName() ?? 'User Google',
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => Hash::make(Str::random(16)),
                    'role' => 'karyawan',
                    'bagian_id' => null,
                    'profile_photo_path' => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                ]);

                Log::info('✅ [Google] User created:', ['user_id' => $user->id]);
            } else {
                Log::info('🟡 [Google] User exists, updating google_id...');

                // ✅ Update google_id jika belum ada
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            }

            // ✅ Login user
            Auth::login($user, true); // true = remember me

            Log::info('✅ [Google] Login success', ['user_id' => $user->id]);

            // ✅ Redirect berdasarkan role
            return match ($user->role) {
                'super' => redirect('/super/dashboard')->with('success', 'Login berhasil!'),
                'admin' => redirect('/admin/dashboard')->with('success', 'Login berhasil!'),
                'karyawan' => redirect('/karyawan/dashboard')->with('success', 'Login berhasil!'),
                default => redirect('/')->with('success', 'Login berhasil!'),
            };

        } catch (InvalidStateException $e) {
            Log::error('❌ [Google] Invalid State Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'hint' => 'Session atau CSRF token tidak valid. Coba clear cache & cookies.',
            ]);

            return redirect('/login')->with('error', 'Session tidak valid. Silakan coba lagi.');

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error('❌ [Google] HTTP Client Error:', [
                'message' => $e->getMessage(),
                'response' => $e->getResponse()?->getBody()->getContents(),
                'hint' => 'Kemungkinan GOOGLE_CLIENT_ID atau GOOGLE_CLIENT_SECRET salah',
            ]);

            return redirect('/login')->with('error', 'Gagal komunikasi dengan Google. Cek konfigurasi OAuth.');

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error('❌ [Google] Connection Error:', [
                'message' => $e->getMessage(),
                'hint' => 'Server tidak bisa akses Google API. Cek firewall/internet.',
            ]);

            return redirect('/login')->with('error', 'Tidak bisa connect ke Google. Cek koneksi internet.');

        } catch (\Exception $e) {
            Log::error('❌ [Google] Unexpected Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'class' => get_class($e),
            ]);

            return redirect('/login')->with('error', 'Login Google gagal: ' . $e->getMessage());
        }
    }
}
