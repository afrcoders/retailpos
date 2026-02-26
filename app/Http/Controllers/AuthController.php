<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Handle web login (session-based)
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        $user = User::where('username', $validated['username'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return back()->withErrors([
                'username' => 'The provided credentials are incorrect.',
            ])->withInput($request->only('username'));
        }

        if (!$user->is_active) {
            return back()->withErrors([
                'username' => 'This user account is inactive.',
            ])->withInput($request->only('username'));
        }

        // Establish session
        Auth::login($user, remember: $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }
}
