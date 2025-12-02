<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate dữ liệu gửi từ frontend
            $request->validate([
                'token' => ['required'],
                'email' => ['required', 'email'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
            $userReseted = null;
            // Xử lý reset password
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user) use ($request ,&$userReseted) {
                    $user->forceFill([
                        'password' => Hash::make($request->string('password')),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                    $userReseted = $user;
                    Auth::login($userReseted);
                }
            );
            // Nếu reset KHÔNG thành công → ném ValidationException
            if ($status !== Password::PASSWORD_RESET) {
                throw ValidationException::withMessages([
                    'email' => [__($status)],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => __($status),
                'user' => $userReseted,

            ], 200);

        } catch (ValidationException $e) {
            // Lỗi validate hoặc token/email sai
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            // Lỗi hệ thống (hiếm khi xảy ra)
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
