<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Menampilkan halaman daftar semua pengguna sistem beserta status aktivitas terakhirnya
    public function index()
    {
        // Mengambil semua user dan melakukan left join dengan tabel sessions untuk mendapatkan waktu aktivitas terakhir
        $users = User::select('users.*', DB::raw('MAX(sessions.last_activity) as last_activity'))
            ->leftJoin('sessions', 'users.id', '=', 'sessions.user_id')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.email_verified_at', 'users.password', 'users.role', 'users.remember_token', 'users.created_at', 'users.updated_at')
            ->get();

        return view('admin.user.index', compact('users'));
    }

    // Menyimpan data pengguna baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,kurator',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    // Memperbarui data pengguna berdasarkan ID
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:admin,kurator',
            'password' => 'nullable|string|min:6',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'User berhasil diperbarui.');
    }

    // Menghapus data pengguna dari database berdasarkan ID
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Mencegah pengguna menghapus akun miliknya sendiri yang sedang aktif
        if (Auth::id() == $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}
