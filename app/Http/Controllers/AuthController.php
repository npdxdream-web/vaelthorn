<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterStat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users',
            'password'       => 'required|min:6|confirmed',
            'char_firstname' => 'required|string|max:100',
            'char_lastname'  => 'required|string|max:100',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $character = Character::create([
            'user_id' => $user->id,
            'name'    => trim($request->char_firstname . ' ' . $request->char_lastname),
            'status'  => 'pending',
        ]);

        CharacterStat::create([
            'character_id' => $character->id,
            'level'        => 0,   // starts at 0 — onboarding gate leads to level 1
            'exp'          => 0,
            'exp_to_next'  => 0,   // not used at level 0; set to exp_to_next.1 on promote
            'hp'           => 100,
            'mana'         => 50,
            'str'          => 10,
            'agi'          => 10,
            'int'          => 10,
        ]);

        Auth::login($user);

        return redirect()->route('onboarding');
    }

public function showLogin()
{
    return view('auth.login');
}

public function login(Request $request)
{
    $request->validate([
        'name'     => 'required|string',
        'password' => 'required',
    ]);

    if (Auth::attempt(['name' => $request->name, 'password' => $request->password])) {
        $request->session()->regenerate();
        return redirect()->route('home');
    }

    return back()->withErrors(['name' => 'ชื่อผู้ใช้หรือ Password ไม่ถูกต้อง']);
}

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
