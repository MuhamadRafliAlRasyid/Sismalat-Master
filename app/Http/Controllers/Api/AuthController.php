<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Alat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /* ================= LOGIN EMAIL/PASSWORD ================= */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string',
            ]);

            // ✅ Ambil alat_id dari berbagai kemungkinan (QR Code Flow)
            $alatHashid = $request->input('alat_id')
                         ?? $request->query('alat_id')
                         ?? $request->route('alat_hashid')
                         ?? $request->header('X-Alat-Id');

            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Email atau password salah'
                ], 401);
            }

            $user = Auth::user();
            $token = $user->createToken('mobile_app')->plainTextToken;

            Log::info('🔐 Mobile Login Success: ' . $user->email .
                      ' | Role: ' . ($user->role ?? 'No role') .
                      ' | Alat HashID: ' . ($alatHashid ?? 'None'));

            $response = [
                'status'  => true,
                'message' => 'Login berhasil',
                'token'   => $token,
                'user'    => $user->only([
                    'id', 'hashid', 'name', 'email', 'role',
                    'bagian_id', 'profile_photo_path'
                ]),
            ];

            // ==================== QR CODE FLOW (ALAT) ====================
            if ($alatHashid) {
                $alatExists = Alat::where('hashid', $alatHashid)->exists();

                $response['alat_id']        = $alatHashid;
                $response['next_screen']    = 'pengambilan_alat.create';
                $response['should_open_form'] = true;
                $response['alat_exists']    = $alatExists;
                $response['message']        = $alatExists
                    ? 'Login berhasil. Silakan lanjutkan pengambilan alat.'
                    : 'Login berhasil. Alat tidak ditemukan, tapi bisa dilanjutkan.';
            }
            // ==================== NORMAL LOGIN ====================
            else {
                $response['next_screen'] = match ($user->role) {
                    'super'    => 'super.dashboard',
                    'admin'    => 'admin.dashboard',
                    'karyawan' => 'karyawan.dashboard',
                    default    => 'home',
                };
                $response['should_open_form'] = false;
            }

            return response()->json($response);

        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Mobile Login Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat login'
            ], 500);
        }
    }

    /**
 * ✅ Google Login untuk Mobile (Flutter)
 * Menggunakan GOOGLE_MOBILE_CLIENT_ID (Web Client ID)
 */
public function googleLoginMobile(Request $request)
{
    try {
        Log::info('🔵 [Google Mobile] Login attempt');

        $request->validate([
            'id_token' => 'required|string',
        ]);

        $idToken = $request->input('id_token');

        // ✅ PENTING: Set config Google Mobile sebelum verifikasi
        // Ini akan override config 'services.google' sementara
        config([
            'services.google.client_id' => config('services.google_mobile.client_id'),
            'services.google.client_secret' => config('services.google_mobile.client_secret'),
        ]);

        Log::info('🔵 [Google Mobile] Using Client ID: ' . config('services.google.client_id'));

        // ✅ Verifikasi ID Token dengan Google
        $googleUser = Socialite::driver('google')
            ->stateless()
            ->userFromToken($idToken);

        Log::info('✅ [Google Mobile] User verified:', [
            'id'    => $googleUser->getId(),
            'email' => $googleUser->getEmail(),
            'name'  => $googleUser->getName(),
        ]);

        // ✅ Validasi email
        if (!$googleUser->getEmail()) {
            return response()->json([
                'status'  => false,
                'message' => 'Email Google tidak tersedia'
            ], 400);
        }

        // ✅ Cek apakah user sudah ada
        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            Log::info('🟡 [Google Mobile] Creating new user...');

            // ✅ Buat user baru
            $user = User::create([
                'name'                => $googleUser->getName() ?? 'User Google',
                'email'               => $googleUser->getEmail(),
                'google_id'           => $googleUser->getId(),
                'password'            => Hash::make(Str::random(16)),
                'role'                => 'karyawan',
                'bagian_id'           => null,
                'profile_photo_path'  => $googleUser->getAvatar(),
                'email_verified_at'   => now(),
            ]);

            Log::info('✅ [Google Mobile] User created: ' . $user->id);
        } else {
            // ✅ Update google_id jika belum ada
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        }

        // ✅ Generate Sanctum token
        $token = $user->createToken('google_mobile')->plainTextToken;

        Log::info('✅ [Google Mobile] Login success', ['user_id' => $user->id]);

        return response()->json([
            'status'  => true,
            'message' => 'Login Google berhasil',
            'token'   => $token,
            'user'    => $user->only([
                'id', 'hashid', 'name', 'email', 'role',
                'bagian_id', 'profile_photo_path'
            ]),
        ]);

    } catch (ValidationException $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Validasi gagal',
            'errors'  => $e->errors()
        ], 422);
    } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
        Log::error('❌ [Google Mobile] Invalid state: ' . $e->getMessage());
        return response()->json([
            'status'  => false,
            'message' => 'Token Google tidak valid'
        ], 401);
    } catch (\Exception $e) {
        Log::error('❌ [Google Mobile] Error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'status'  => false,
            'message' => 'Login Google gagal: ' . $e->getMessage()
        ], 500);
    }
}

    /* ================= REGISTER ================= */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name'      => 'required|string|max:255',
                'email'     => 'required|email|unique:users,email',
                'password'  => 'required|string|min:6',
                'role'      => 'required|in:admin,karyawan',
                'bagian_id' => 'nullable|exists:bagian,id',
            ]);

            $user = User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role'      => $request->role,
                'bagian_id' => $request->bagian_id,
            ]);

            $token = $user->createToken('mobile_app')->plainTextToken;

            return response()->json([
                'status'  => true,
                'message' => 'Registrasi berhasil',
                'token'   => $token,
                'user'    => $user->only([
                    'id', 'hashid', 'name', 'email', 'role', 'bagian_id'
                ])
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Register Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat registrasi'
            ], 500);
        }
    }

    /* ================= LOGOUT ================= */
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json([
                'status'  => true,
                'message' => 'Logout berhasil'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Logout Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Gagal logout'
            ], 500);
        }
    }

    /* ================= PROFILE ================= */
    public function profile(Request $request)
    {
        return response()->json([
            'status' => true,
            'user'   => $request->user()->only([
                'id', 'hashid', 'name', 'email', 'role',
                'bagian_id', 'profile_photo_path', 'profile_photo_url'
            ])
        ]);
    }

    /* ================= UPDATE PROFILE ================= */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            $request->validate([
                'name'     => 'sometimes|string|max:255',
                'password' => 'sometimes|nullable|string|min:6',
            ]);

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->name = $request->name ?? $user->name;
            $user->save();

            return response()->json([
                'status'  => true,
                'message' => 'Profil berhasil diperbarui',
                'user'    => $user->only([
                    'id', 'hashid', 'name', 'email', 'role', 'bagian_id'
                ])
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Update Profile Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memperbarui profil'
            ], 500);
        }
    }
}
