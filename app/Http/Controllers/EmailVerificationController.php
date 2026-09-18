<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;

class EmailVerificationController extends Controller
{
    public function send(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return back()->with('success', 'Email already verified.');
        }

        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $throttleKey = 'verify-email-' . $user->id;
        if (Cache::has($throttleKey)) {
            return back()->with('error', 'Verification email already sent. Please check your inbox or wait a minute before resending.');
        }

        try {
            Mail::send([], [], function ($message) use ($user, $verifyUrl) {
                $message->to($user->email)
                    ->subject('Verify your email — ReviewFlow')
                    ->html(
                        '<div style="font-family:system-ui,sans-serif;max-width:480px;margin:0 auto;padding:32px;">'
                        . '<div style="width:48px;height:48px;border-radius:12px;background:#4c6fff;color:#fff;display:grid;place-items:center;font-weight:700;font-size:20px;margin-bottom:20px;">R</div>'
                        . '<h2 style="margin:0 0 8px;">Verify your email</h2>'
                        . '<p style="color:#6f7d78;margin:0 0 24px;line-height:1.6;">Click the button below to verify your email address and unlock all ReviewFlow features.</p>'
                        . '<a href="' . $verifyUrl . '" style="display:inline-block;padding:12px 28px;background:#4c6fff;color:#fff;border-radius:10px;text-decoration:none;font-weight:600;">Verify Email Address</a>'
                        . '<p style="color:#999;font-size:12px;margin-top:24px;">If you didn\'t create an account, no action is needed.</p>'
                        . '</div>'
                    );
            });

            Cache::put($throttleKey, true, 60);

            return back()->with('success', 'Verification link sent to ' . $user->email);
        } catch (\Exception $e) {
            return back()->with('error', 'Could not send email. Please check your mail configuration.');
        }
    }

    public function verify(Request $request, $id, $hash)
    {
        $user = \App\Models\User::findOrFail($id);

        if (! hash_equals($hash, sha1($user->email))) {
            abort(403, 'Invalid verification link.');
        }

        if (! $request->hasValidSignature()) {
            return redirect()->route('dashboard')->with('error', 'Verification link has expired. Please request a new one.');
        }

        if (! $user->email_verified_at) {
            // email_verified_at isn't mass-assignable — set it directly so it persists.
            $user->email_verified_at = now();
            $user->save();
        }

        return redirect()->route('dashboard')->with('success', 'Email verified successfully! ✓');
    }

    public function changeEmail(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
        ]);

        $request->user()->update([
            'email' => $data['email'],
            'email_verified_at' => null,
        ]);

        return back()->with('success', 'Email updated to ' . $data['email'] . '. Please verify your new email.');
    }
}
