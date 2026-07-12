<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bagian;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Tampilkan daftar pengguna (selain admin) beserta relasi bagian.
     */
    public function index(Request $request)
    {
        $query = User::with('bagian')->where('role', '!=', 'admin');

        if ($request->has('search')) {
            $search = $request->input('search');
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

        return view('admin.index', compact('users'));
    }

    /**
     * Tampilkan form untuk membuat user baru.
     */
    public function create()
    {
        $bagians = Bagian::all();
        return view('admin.create', compact('bagians'));
    }

    /**
     * Simpan user baru ke database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
                'bagian_id' => 'nullable|exists:bagian,id',
                'role' => 'required|in:super,admin,karyawan',
            ]);

            $validated['password'] = Hash::make($validated['password']);
            $user = User::create($validated);
            $this->handleFileUpload($request, $user);

            $redirectRoute = optional(Auth::user())->role === 'admin'
                ? 'admin.index'
                : 'permintaan.index';

            Log::info('User created successfully', [
                'action' => 'CREATE',
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'created_by' => Auth::user()->email ?? 'system',
            ]);

            return redirect()->route($redirectRoute)->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating user', [
                'action' => 'CREATE',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except('password'),
            ]);
            return redirect()->back()->with('error', 'Gagal membuat user. Silakan coba lagi.');
        }
    }

    /**
     * Tampilkan form edit data user.
     */
    public function edit(User $user) // Laravel otomatis decode hashid → $user
    {
        $bagians = Bagian::all();
        return view('admin.edit', compact('user', 'bagians'));
    }

    /**
     * Perbarui data user.
     */
    /* ================= UPDATE ================= */
public function update(Request $request, User $user)
{
    Log::info('🔍 [UserController@update] Request for user_id: ' . $user->id, [
        'data' => $request->except(['password', 'profile_photo']),
    ]);

    try {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            // ✅ UBAH: email jadi optional (sometimes), unik kecuali user ini
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
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

        // ✅ Set default role jika tidak ada
        if (empty($validated['role'])) {
            $validated['role'] = $user->role; // ✅ Pakai role existing
        }

        // ✅ Set default bagian_id jika tidak ada
        if (!array_key_exists('bagian_id', $validated)) {
            $validated['bagian_id'] = $user->bagian_id; // ✅ Pakai bagian existing
        }

        // ✅ Handle password
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']); // ✅ Jangan update password jika kosong
        }

        // ✅ Handle file upload
        $validated = $this->handleFileUpload($request, $user, $validated);
        $user->update($validated);

        // ✅ Generate URL foto
        $user->profile_photo_url = $this->getPhotoUrl($user);

        Log::info('✅ [UserController@update] Success', [
            'user_id' => $user->id,
            'photo_url' => $user->profile_photo_url,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diperbarui.',
            'data' => $user
        ]);
    } catch (\Exception $e) {
        Log::error('❌ [UserController@update] Error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Gagal memperbarui user: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Hapus user.
     */
    public function destroy(User $user)
    {
        try {
            // Simpan data untuk log sebelum dihapus
            $logData = [
                'action' => 'DELETE',
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'deleted_by' => Auth::user()->email ?? 'system',
            ];

            // Hapus foto profil jika ada
            if ($user->profile_photo_path) {
                $path = public_path('images/profile/' . $user->profile_photo_path);
                if (File::exists($path)) {
                    File::delete($path);
                }
            }

            $user->delete();

            Log::info('User deleted successfully', $logData);

            return redirect()->route('admin.index')->with('success', 'User deleted.');
        } catch (\Exception $e) {
            Log::error('Error deleting user', [
                'action' => 'DELETE',
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Gagal menghapus user. Silakan coba lagi.');
        }
    }

    /**
     * Handle upload foto profil user.
     */
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

            if ($user->profile_photo_path && File::exists($directory . '/' . $user->profile_photo_path)) {
                File::delete($directory . '/' . $user->profile_photo_path);
            }

            $validated['profile_photo_path'] = $filename;
        } else {
            $validated['profile_photo_path'] = $user->profile_photo_path;
        }

        return $validated;
    }
}
