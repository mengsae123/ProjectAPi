<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'device_name' => ['sometimes', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $tokenName = trim($validated['device_name'] ?? ($request->userAgent() ?: 'api'));
        if ($tokenName === '') {
            $tokenName = 'api';
        }
        $tokenName = substr($tokenName, 0, 255);
        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'message' => 'Register success',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['sometimes', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $tokenName = trim($validated['device_name'] ?? ($request->userAgent() ?: 'api'));
        if ($tokenName === '') {
            $tokenName = 'api';
        }
        $tokenName = substr($tokenName, 0, 255);
        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'message' => 'Login success',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        if ($token) {
            $token->delete();
        } elseif ($user) {
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => 'Logout success',
        ], 200);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ], 200);
    }
}
