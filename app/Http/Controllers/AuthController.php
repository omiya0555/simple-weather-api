<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
    
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
    
            return response()->json(['message' => 'Logged in successfully'], 200);
        }
    
        return response()->json(['message' => 'Login failed'], 401);
    }
}
