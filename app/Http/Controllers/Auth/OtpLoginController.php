<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpLoginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/** Email-OTP login for Super Admin, every role panel and Employee. */
class OtpLoginController extends Controller
{
    public function __construct(private OtpLoginService $otp) {}

    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'context'    => 'required|in:superadmin,role,employee',
            'identifier' => 'required|email|max:150',
            'role_slug'  => 'nullable|string|max:100',
        ]);

        $account = $this->otp->findAccount($data['context'], $data['identifier'], $data['role_slug'] ?? null);

        if (!$account) {
            Log::warning('Login OTP: account not found', ['context' => $data['context']]);
        } else {
            $result = $this->otp->send($account);

            if (str_starts_with($result, 'mail_failed:')) {
                $message = config('app.debug')
                    ? trim(substr($result, strlen('mail_failed:')))
                    : 'Unable to send the OTP right now. Please try again.';

                return response()->json([
                    'ok' => false,
                    'message' => $message,
                ]);
            }
        }

        // same answer whether or not the account exists (no account enumeration)
        return response()->json([
            'ok'      => true,
            'message' => 'If this account exists, a 6-digit OTP has been sent to its registered email.',
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'context'    => 'required|in:superadmin,role,employee',
            'identifier' => 'required|email|max:150',
            'role_slug'  => 'nullable|string|max:100',
            'otp'        => 'required|digits:6',
        ]);

        $account = $this->otp->findAccount($data['context'], $data['identifier'], $data['role_slug'] ?? null);

        if (!$account || !$this->otp->verify($account, $data['otp'])) {
            return response()->json(['ok' => false, 'message' => 'Invalid or expired OTP.'], 422);
        }

        Auth::guard($account['guard'])->login($account['user']);
        $request->session()->regenerate();

        if ($data['context'] === 'employee') {
            $redirect = route('employee.dashboard');
        } elseif ($data['context'] === 'role') {
            $request->session()->put('login_role_slug', $data['role_slug']);
            $redirect = url('/' . $data['role_slug'] . '/home');
        } else {
            $request->session()->forget('login_role_slug');
            $redirect = route('home');
        }

        return response()->json(['ok' => true, 'redirect' => $redirect]);
    }
}
