<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Check if email already verified
            if ($request->user()->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email has already been verified.',
                    'success' => true
                ], 200);
            }

            // Send verification email
            $request->user()->sendEmailVerificationNotification();

            return response()->json([
                'message' => 'Verification email has been resent.'
            ], 200);

        } catch (\Exception $e) {
            // Catch all errors (mail, unexpected issues...)
            return response()->json([
                'message' => 'Failed to send verification email.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
