<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Validate đầu vào
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            // Tạo user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Gửi event đăng ký
            event(new Registered($user));

            // Đăng nhập tự động
            Auth::login($user);

            // === 201 CREATED (Chuẩn REST) ===
            return response()->json([
                'message' => 'Ông cháu đăng ký xong rồi đấy',
                'user' => $user,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // === 422 UNPROCESSABLE ENTITY ===
            return response()->json([
                'message' => 'Invalid data',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Illuminate\Database\QueryException $e) {
            // Lỗi duplicate key hoặc lỗi DB
            if ($e->getCode() === '23000') {
                // === 409 CONFLICT ===
                return response()->json([
                    'message' => 'Email already exists',
                ], 409);
            }

            // === 500 INTERNAL SERVER ERROR ===
            return response()->json([
                'message' => 'Database error',
                'error' => $e->getMessage(),
            ], 500);

        } catch (\Exception $e) {
            // === 500 INTERNAL SERVER ERROR ===
            return response()->json([
                'message' => 'Unexpected error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
