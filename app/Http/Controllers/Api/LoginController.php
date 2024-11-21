<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function __invoke(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Ambil kredensial
        $credentials = $request->only('email', 'password');

        // Jika autentikasi gagal
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password Anda salah'
            ], 401);
        }

        $user = auth()->user();
        $customClaims = [
            'id' => $user->id,
            'name' => $user->name,
        ];

        $token = JWTAuth::claims($customClaims)->fromUser($user);

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token
        ], 200);
    }

    public function refreshToken(Request $request)
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();

            return response()->json([
                'success' => true,
                'token' => $newToken
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed',
            ], 401);
        }
    }
}
