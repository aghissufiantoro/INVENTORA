<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = User::all();
        return view('auth.account.index', compact('accounts'));
    }

    public function create()
    {
        return view('auth.account.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username',
            'password' => 'required|min:6',
            'role' => 'required',
        ]);

        User::create([
            'name' => $request->username,
            'username' => $request->username,
            'email' => $request->email, // ✅ isi otomatis biar gak NULL
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('account.index')->with('success', 'Akun berhasil dibuat.');
    }


    public function edit($id)
    {
        $account = User::findOrFail($id);
        return view('auth.account.edit', compact('account'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'username' => 'required|unique:users,username,' . $id,
            'role' => 'required',
        ]);

        $data = [
            'name' => $request->username,
            'username' => $request->username,
            'email' => $request->email, // tetap isi otomatis
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect()->route('account.index')->with('success', 'Akun berhasil diperbarui.');
    }


    public function destroy($id)
    {
        User::destroy($id);
        return redirect()->route('account.index')->with('success', 'Akun berhasil dihapus');
    }
}
