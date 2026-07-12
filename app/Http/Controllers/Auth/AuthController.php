<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Bagian;
use App\Models\Spareparts;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister()
    {
        $bagians = Bagian::all();
        return view('auth.register', compact('bagians'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,karyawan,super',
            'bagian_id' => 'nullable|exists:bagians,id',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['name', 'email', 'role', 'bagian_id']);
        $data['password'] = Hash::make($request->password);

        if ($request->hasFile('profile_photo')) {
            $directory = public_path('images/profile');
            if (!file_exists($directory)) mkdir($directory, 0755, true);

            $fileName = Str::random(10) . '.' . $request->file('profile_photo')->getClientOriginalExtension();
            $request->file('profile_photo')->move($directory, $fileName);
            $data['profile_photo_path'] = $fileName;
        }

        User::create($data);

        return match ($data['role']) {
            'super' => redirect('/super/dashboard')->with('success', 'Registrasi berhasil!'),
            'admin' => redirect('/admin/dashboard')->with('success', 'Registrasi berhasil!'),
            'karyawan' => redirect('/karyawan/dashboard')->with('success', 'Registrasi berhasil!'),
            default => redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.'),
        };
    }

    public function showLoginForm()
    {
        // Tangkap spareparts_id (jika ada) -> simpan di session
        $sparepartsHashid = request()->query('spareparts_id');
        if ($sparepartsHashid) {
            session(['spareparts_hashid' => $sparepartsHashid]);
        }

        // ✅ Tangkap alat_id (jika ada) -> simpan di session
        $alatHashid = request()->query('alat_id');
        if ($alatHashid) {
            session(['alat_hashid' => $alatHashid]);
        }

        return view('auth.login', compact('sparepartsHashid', 'alatHashid'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('success', 'Anda telah logout.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Ambil dari session (bisa juga dari request/query sebagai fallback)
        $sparepartsHashid = session('spareparts_hashid') ?? $request->input('spareparts_id') ?? $request->query('spareparts_id');
        $alatHashid = session('alat_hashid') ?? $request->input('alat_id') ?? $request->query('alat_id');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $role = Auth::user()->role;

            Log::info('User logged in: ' . Auth::user()->email . ', Role: ' . ($role ?? 'No role') . ', Spareparts HashID: ' . ($sparepartsHashid ?? 'None') . ', Alat HashID: ' . ($alatHashid ?? 'None'));

            // Hapus session setelah dibaca
            $request->session()->forget(['spareparts_hashid', 'alat_hashid']);

            // Redirect ke form pengambilan dengan parameter yang sesuai
            if ($alatHashid) {
                return redirect()->route('pengambilan_alat.create', ['alat_id' => $alatHashid])->with('success', 'Login berhasil! Silakan ambil alat.');
            }

            if ($sparepartsHashid) {
                return redirect()->route('pengambilan.create', ['spareparts_id' => $sparepartsHashid])->with('success', 'Login berhasil!');
            }

            // Jika tidak ada parameter khusus, redirect sesuai role
            return match ($role) {
                'super' => redirect('/super/dashboard')->with('success', 'Login berhasil!'),
                'admin' => redirect('/admin/dashboard')->with('success', 'Login berhasil!'),
                'karyawan' => redirect('/karyawan/dashboard')->with('success', 'Login berhasil!'),
                default => redirect('/')->with('success', 'Login berhasil!'),
            };
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput();
    }
}
