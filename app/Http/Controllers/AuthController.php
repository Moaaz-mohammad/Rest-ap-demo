<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function register(Request $request) {

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'])
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ]);
        
    }

    public function login(Request $request) {
        
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // throw ValidationException::withMessages([
            //     'email' => ['The provided credentials are incorrect'],
            // ]);  
            return response()->json([
                'message' => "Invalid credentials"
            ], 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;


        return response()->json([
            'token' => $token,
            'user' => $user
        ]);

    }

    public function logout(Request $request) {
        
        $request->user()->currentAccessToken()->delete();
        
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out'
        ]);

    }
}
