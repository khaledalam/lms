<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string'
        ]);

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }

        $user = $request->user();
        $user->tokens()->delete(); // optional: revoke old tokens
        $token = $user->createToken($credentials['device_name'] ?? 'postman')->plainTextToken;

        return response()->json(['token' => $token]);
    }
}
