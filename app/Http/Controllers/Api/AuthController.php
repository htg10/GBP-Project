<?php

namespace App\Http\Controllers\Api;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Client;
use App\Models\Subscription;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::once($data)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }

        $user = Auth::user();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function googleLogin(Request $request)
    {
        $data = $request->validate([
            'id_token' => 'required|string',
        ]);

        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $data['id_token'],
        ]);

        if (!$response->ok()) {
            return response()->json(['message' => 'Invalid Google token'], 401);
        }

        $info = $response->json();

        if (
            ($info['aud'] ?? null) !== config('services.google_mobile.client_id')
            || ($info['email_verified'] ?? 'false') !== 'true'
        ) {
            return response()->json(['message' => 'Invalid Google token'], 401);
        }

        $user = User::where('email', $info['email'])->first();

        if (!$user) {
            $name = $info['name'] ?? $info['email'];

            $user = DB::transaction(function () use ($info, $name) {
                $agency = Agency::create([
                    'name' => $name . "'s Business",
                    'slug' => Str::slug($name) . '-' . Str::lower(Str::random(5)),
                ]);

                $client = Client::create([
                    'agency_id' => $agency->id,
                    'name' => $name . "'s Business",
                    'email' => $info['email'],
                ]);

                $user = User::create([
                    'agency_id' => $agency->id,
                    'name' => $name,
                    'email' => $info['email'],
                    'password' => Hash::make(Str::random(24)),
                    'role' => 'CLIENT_OWNER',
                    'client_id' => $client->id,
                    'avatar' => $info['picture'] ?? null,
                ]);

                Subscription::create([
                    'agency_id' => $agency->id,
                    'plan' => 'STARTER',
                    'status' => 'TRIALING',
                ]);

                return $user;
            });
        }

        $token = $user->createToken('mobile')->plainTextToken;

        $code = Str::random(64);
        Cache::put('mobile_login:' . $code, $user->id, now()->addSeconds(60));

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
            'login_url' => route('mobile.login', ['code' => $code]),
        ]);
    }

    public function webSession(Request $request)
    {
        $code = Str::random(64);
        Cache::put('mobile_login:' . $code, $request->user()->id, now()->addSeconds(60));

        return response()->json([
            'login_url' => route('mobile.login', ['code' => $code]),
        ]);
    }

    public function sendVerification(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email already verified.'], 422);
        }

        $throttleKey = 'verify-email-' . $user->id;
        if (Cache::has($throttleKey)) {
            return response()->json(['message' => 'Verification email already sent. Please wait a minute before resending.'], 429);
        }

        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        try {
            Mail::send([], [], function ($message) use ($user, $verifyUrl) {
                $message->to($user->email)
                    ->subject('Verify your email — ReviewFlow')
                    ->html(
                        '<div style="font-family:system-ui,sans-serif;max-width:480px;margin:0 auto;padding:32px;">'
                        . '<h2 style="margin:0 0 8px;">Verify your email</h2>'
                        . '<p style="color:#6f7d78;margin:0 0 24px;line-height:1.6;">Click the button below to verify your email address and unlock all ReviewFlow features.</p>'
                        . '<a href="' . $verifyUrl . '" style="display:inline-block;padding:12px 28px;background:#4c6fff;color:#fff;border-radius:10px;text-decoration:none;font-weight:600;">Verify Email Address</a>'
                        . '</div>'
                    );
            });

            Cache::put($throttleKey, true, 60);

            return response()->json(['success' => true, 'message' => 'Verification link sent to ' . $user->email]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not send email. Please check the mail configuration.'], 500);
        }
    }

    public function changeEmail(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->email = $data['email'];
        $user->email_verified_at = null;
        $user->save();

        return response()->json(['success' => true, 'email' => $user->email]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $agency = Agency::create(['name' => $data['name'] . "'s Agency"]);

        $user = \App\Models\User::create([
            'agency_id' => $agency->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'SUPER_ADMIN',
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true]);
    }

    public function me(Request $request)
    {
        return response()->json($this->userPayload($request->user()));
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'avatar' => 'nullable|string',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:8',
        ]);

        $user = $request->user();
        $user->name = $data['name'];
        $user->phone = $data['phone'] ?? $user->phone;

        if (!empty($data['avatar'])) {
            $user->avatar = $data['avatar'];
        }

        if (!empty($data['new_password'])) {
            if (empty($data['current_password']) || !Hash::check($data['current_password'], $user->password)) {
                return response()->json(['message' => 'Current password is incorrect.'], 422);
            }
            $user->password = $data['new_password'];
        }

        $user->save();

        return response()->json($this->userPayload($user));
    }

    public function subscription(Request $request, CreditService $credits)
    {
        $agencyId = $request->user()->agency_id;
        $sub = Subscription::where('agency_id', $agencyId)->first();

        return response()->json([
            'plan' => $sub->plan ?? null,
            'status' => $sub->status ?? null,
            'credit_balance' => $credits->balance($agencyId),
            'monthly_credits' => $sub->monthly_credits ?? 0,
            'renews_at' => $sub->renews_at ?? null,
            'auto_reply' => (bool) ($sub->auto_reply ?? false),
            'wa_notify' => (bool) ($sub->wa_notify ?? false),
        ]);
    }

    private function userPayload($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'avatar' => $user->avatar,
            'phone' => $user->phone,
            'agency_id' => $user->agency_id,
            'client_id' => $user->client_id,
            'is_admin' => $user->isAdmin(),
            'email_verified' => (bool) $user->email_verified_at,
        ];
    }
}
