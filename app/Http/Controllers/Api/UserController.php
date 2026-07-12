<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Bagian;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /* ================= HELPER: Generate Photo URL ================= */
    /**
     * ✅ Mengembalikan URL foto profile yang benar
     * 
     * Prioritas:
     * 1. URL eksternal (Google, ui-avatars) → return langsung
     * 2. URL localhost yang rusak (prefix ganda) → extract URL asli
     * 3. URL localhost yang valid → return path relatif
     * 4. Path relatif → return dengan prefix images/profile/
     */
    private function getPhotoUrl($user)
    {
        // 1. Cek field google_avatar (untuk user yang login via Google)
        if (!empty($user->google_avatar)) {
            $googleAvatar = $user->google_avatar;
            
            // Jika sudah URL valid, return langsung
            if (str_starts_with($googleAvatar, 'https://') || 
                str_starts_with($googleAvatar, 'http://')) {
                return $googleAvatar;
            }
        }
        
        // 2. Cek profile_photo_path
        if (!empty($user->profile_photo_path)) {
            $path = $user->profile_photo_path;
            
            // ✅ PRIORITAS 1: URL eksternal (Google, ui-avatars, dll)
            // Cek apakah path mengandung URL eksternal
            if (preg_match('/(https?:\/\/[^\s]+)/', $path, $matches)) {
                $extractedUrl = $matches[1];
                
                // Jika URL eksternal (Google, ui-avatars, dll), return langsung
                if (str_contains($extractedUrl, 'googleusercontent') || 
                    str_contains($extractedUrl, 'ui-avatars') ||
                    str_contains($extractedUrl, 'google.com') ||
                    str_contains($extractedUrl, 'lh3.') ||
                    str_contains($extractedUrl, 'gstatic')) {
                    return $extractedUrl;
                }
            }
            
            // ✅ PRIORITAS 2: Handle URL localhost yang rusak
            // Contoh: http://127.0.0.1:8000/storage/https://lh3.googleusercontent.com/...
            if (str_starts_with($path, 'http://127.0.0.1') || 
                str_starts_with($path, 'http://localhost')) {
                
                // Extract URL setelah /storage/ atau /images/
                if (preg_match('/\/storage\/(https?:\/\/.+)$/', $path, $matches)) {
                    return $matches[1]; // Return URL eksternal langsung
                }
                
                if (preg_match('/\/images\/profile\/(.+)$/', $path, $matches)) {
                    return 'images/profile/' . $matches[1];
                }
                
                if (preg_match('/\/storage\/(.+)$/', $path, $matches)) {
                    $extractedPath = $matches[1];
                    // Jika path yang diekstrak adalah URL eksternal, return langsung
                    if (str_starts_with($extractedPath, 'http://') || 
                        str_starts_with($extractedPath, 'https://')) {
                        return $extractedPath;
                    }
                    return 'storage/' . $extractedPath;
                }
            }
            
            // ✅ PRIORITAS 3: URL http/https yang valid (bukan localhost)
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            
            // ✅ PRIORITAS 4: Path relatif
            // Jika sudah ada prefix yang benar, return langsung
            if (str_starts_with($path, 'images/profile/') || 
                str_starts_with($path, 'storage/')) {
                return $path;
            }
            
            // Default: anggap sebagai filename di folder images/profile/
            return 'images/profile/' . $path;
        }
        
        // 3. Tidak ada foto
        return null;
    }

    /* ================= INDEX ================= */
    public function index(Request $request)
    {
        Log::info('🔍 [UserController@index] Request received', [
            'params' => $request->all(),
            'user_id' => auth()->id(),
        ]);

        try {
            $query = User::with('bagian')->where('role', '!=', 'admin');

            if ($request->has('search')) {
                $search = $request->input('search');
                Log::info('🔍 [UserController@index] Searching: ' . $search);
                
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%")
                        ->orWhereHas('bagian', function ($q) use ($search) {
                            $q->where('nama', 'like', "%{$search}%");
                        });
                });
            }

            $users = $query->paginate(10)->withQueryString();

            // ✅ Transform data dengan helper getPhotoUrl
            $users->getCollection()->transform(function ($user) {
                $user->profile_photo_url = $this->getPhotoUrl($user);
                return $user;
            });

            Log::info('✅ [UserController@index] Success', [
                'total' => $users->total(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [UserController@index] Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pengguna: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /* ================= CREATE ================= */
    public function create()
    {
        Log::info('🔍 [UserController@create] Request received');

        try {
            $bagians = Bagian::all();

            return response()->json([
                'success' => true,
                'data' => [
                    'bagians' => $bagians
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [UserController@create] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data form: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= STORE ================= */
    public function store(Request $request)
    {
        Log::info('🔍 [UserController@store] Request received', [
            'data' => $request->except(['password', 'profile_photo']),
            'has_file' => $request->hasFile('profile_photo'),
        ]);

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
                'bagian_id' => 'nullable|exists:bagian,id',
                'role' => 'required|in:super,admin,karyawan',
                'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'google_avatar' => 'nullable|url',
            ]);

            if ($validator->fails()) {
                Log::warning('⚠️ [UserController@store] Validation failed', [
                    'errors' => $validator->errors(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();
            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            // Handle file upload
            $validated = $this->handleFileUpload($request, $user, $validated);
            $user->update($validated);

            // ✅ Pakai helper untuk generate URL
            $user->profile_photo_url = $this->getPhotoUrl($user);

            Log::info('✅ [UserController@store] Success', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'photo_url' => $user->profile_photo_url,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully.',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            Log::error('❌ [UserController@store] Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat user: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= SHOW ================= */
    public function show(User $user)
    {
        Log::info('🔍 [UserController@show] Request for user_id: ' . $user->id);

        try {
            // ✅ Pakai helper untuk generate URL
            $user->profile_photo_url = $this->getPhotoUrl($user);

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [UserController@show] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data user: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= EDIT ================= */
    public function edit(User $user)
    {
        Log::info('🔍 [UserController@edit] Request for user_id: ' . $user->id);

        try {
            $bagians = Bagian::all();

            // ✅ Pakai helper untuk generate URL
            $user->profile_photo_url = $this->getPhotoUrl($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'bagians' => $bagians
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [UserController@edit] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= UPDATE ================= */
    public function update(Request $request, User $user)
    {
        Log::info('🔍 [UserController@update] Request for user_id: ' . $user->id, [
            'data' => $request->except(['password', 'profile_photo']),
        ]);

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:6',
                'bagian_id' => 'nullable|exists:bagian,id',
                'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'role' => 'sometimes|in:super,admin,karyawan',
                'google_avatar' => 'nullable|url',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            if (empty($validated['role'])) {
                $validated['role'] = 'karyawan';
            }

            if ($request->filled('password')) {
                $validated['password'] = Hash::make($request->password);
            } else {
                unset($validated['password']);
            }

            // Handle file upload
            $validated = $this->handleFileUpload($request, $user, $validated);
            $user->update($validated);

            // ✅ Pakai helper untuk generate URL
            $user->profile_photo_url = $this->getPhotoUrl($user);

            Log::info('✅ [UserController@update] Success', [
                'user_id' => $user->id,
                'photo_url' => $user->profile_photo_url,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User updated.',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [UserController@update] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui user: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= DESTROY ================= */
    public function destroy(User $user)
    {
        Log::info('🔍 [UserController@destroy] Request for user_id: ' . $user->id);

        try {
            if ($user->profile_photo_path) {
                $path = public_path('images/profile/' . $user->profile_photo_path);
                if (File::exists($path)) {
                    File::delete($path);
                    Log::info('🗑️ [UserController@destroy] Photo deleted: ' . $path);
                }
            }

            $user->delete();

            Log::info('✅ [UserController@destroy] Success', [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User deleted.'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [UserController@destroy] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= GET PROFILE (User yang sedang login) ================= */
    public function getProfile()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        Log::info('🔍 [UserController@getProfile] Request for user_id: ' . $user->id);

        try {
            $user->load('bagian');
            $user->profile_photo_url = $this->getPhotoUrl($user);

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [UserController@getProfile] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil profil: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= UPDATE PROFILE (User yang sedang login) ================= */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        Log::info('🔍 [UserController@updateProfile] Request for user_id: ' . $user->id);

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:6',
                'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            if ($request->filled('password')) {
                $validated['password'] = Hash::make($request->password);
            } else {
                unset($validated['password']);
            }

            // Handle file upload
            $validated = $this->handleFileUpload($request, $user, $validated);
            $user->update($validated);

            // ✅ Pakai helper untuk generate URL
            $user->profile_photo_url = $this->getPhotoUrl($user);

            Log::info('✅ [UserController@updateProfile] Success', [
                'user_id' => $user->id,
                'photo_url' => $user->profile_photo_url,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated.',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [UserController@updateProfile] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui profil: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= HANDLE FILE UPLOAD ================= */
    private function handleFileUpload(Request $request, User $user, array $validated = [])
    {
        if ($request->hasFile('profile_photo')) {
            $directory = public_path('images/profile');
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            $file = $request->file('profile_photo');
            $filename = Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $filename);

            // Hapus foto lama jika ada
            if ($user->profile_photo_path) {
                $oldPath = $directory . '/' . $user->profile_photo_path;
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                    Log::info('🗑️ [handleFileUpload] Old photo deleted: ' . $oldPath);
                }
            }

            $validated['profile_photo_path'] = $filename;
            Log::info('📷 [handleFileUpload] New photo uploaded: ' . $filename);
        } else {
            // Pertahankan foto lama jika tidak ada upload baru
            $validated['profile_photo_path'] = $user->profile_photo_path;
        }

        return $validated;
    }
}