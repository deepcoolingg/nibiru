<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\ResendOtpRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Requests\Api\VerifyOtpRequest;
use App\Models\ActivityLog;
use App\Models\EmailVerificationOtp;
use App\Models\User;
use Google\Client as GoogleClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $birthday = Carbon::createFromFormat(
                '!j-F-Y',
                sprintf(
                    '%d-%s-%d',
                    (int) $request->validated('birthday.day'),
                    (string) $request->validated('birthday.month'),
                    (int) $request->validated('birthday.year'),
                ),
            )->toDateString();
        } catch (Throwable $throwable) {
            throw ValidationException::withMessages([
                'birthday' => ['Invalid birthday. Please provide a valid date.'],
            ]);
        }

        $user = User::query()->create([
            'name' => $request->validated('name'),
            'email' => strtolower((string) $request->validated('email')),
            'phone_number' => $request->validated('phone_number'),
            'birthday' => $birthday,
            'password' => $request->validated('password'),
            'role' => 'customer',
            'is_active' => false,
        ]);

        $otpCode = $this->issueOtpForUser($user);
        $this->sendOtpEmail($user, $otpCode);

        ActivityLog::log('user_registered', "User {$user->email} registered.", $user->id);

        return response()->json([
            'message' => 'Registration successful. Please verify your email with the OTP code.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'birthday' => $user->birthday?->toDateString(),
                'is_active' => $user->is_active,
            ],
        ], 201);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $user = User::query()->where('email', strtolower((string) $request->validated('email')))->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['No account found for this email.'],
            ]);
        }

        $otpRecord = $user->emailVerificationOtp;

        if (! $otpRecord) {
            throw ValidationException::withMessages([
                'otp' => ['OTP not found. Please request a new OTP.'],
            ]);
        }

        if ($otpRecord->expires_at->isPast()) {
            $otpRecord->delete();

            throw ValidationException::withMessages([
                'otp' => ['OTP has expired. Please request a new OTP.'],
            ]);
        }

        if (! Hash::check($request->validated('otp'), $otpRecord->otp_code_hash)) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid OTP code.'],
            ]);
        }

        $user->forceFill([
            'is_active' => true,
            'email_verified_at' => now(),
        ])->save();

        $otpRecord->delete();

        $token = $user->createToken('api')->plainTextToken;

        ActivityLog::log('user_verified_email_otp', "User {$user->email} verified email OTP.", $user->id);

        return response()->json([
            'message' => 'Email verification successful.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'birthday' => $user->birthday?->toDateString(),
                'is_active' => $user->is_active,
            ],
            'token' => $token,
        ]);
    }

    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['No account found for this email.'],
            ]);
        }

        if ($user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Account is already verified.'],
            ]);
        }

        $otpCode = $this->issueOtpForUser($user);
        $this->sendOtpEmail($user, $otpCode);

        ActivityLog::log('user_resend_email_otp', "User {$user->email} requested OTP resend.", $user->id);

        return response()->json([
            'message' => 'A new OTP has been sent to your email.',
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        abort_unless($user->is_active, 403, 'Account is not verified. Please verify OTP first.');

        $token = $user->createToken('api')->plainTextToken;

        ActivityLog::log('user_login', "User {$user->email} logged in.", $user->id);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'birthday' => $user->birthday?->toDateString(),
                'is_active' => $user->is_active,
            ],
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        ActivityLog::log('user_logout', "User {$request->user()->email} logged out.", $request->user()->id);

        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->load('orders.orderItems.product', 'carts');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'birthday' => $user->birthday?->toDateString(),
                'role' => $user->role,
                'is_active' => $user->is_active,
                'orders' => $user->orders->map(fn ($order) => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total_price' => $order->grand_total,
                    'shipping_address' => $order->shipping_address,
                    'country' => $order->country,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    'items' => $order->orderItems->map(fn ($item) => [
                        'image' => $item->product ? route('api.products.image', ['product' => $item->product->id]) : null,
                        'image_url' => $item->product ? route('api.products.image', ['product' => $item->product->id]) : null,
                        'imageUrl' => $item->product ? route('api.products.image', ['product' => $item->product->id]) : null,
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->qty,
                        'qty' => $item->qty,
                        'price' => $item->price_at_checkout,
                        'price_at_checkout' => $item->price_at_checkout,
                        'product_name' => $item->product_name,
                        'product' => [
                            'id' => $item->product?->id,
                            'name' => $item->product?->name ?? $item->product_name,
                            'price' => $item->product?->price,
                            'image' => $item->product ? route('api.products.image', ['product' => $item->product->id]) : null,
                            'image_url' => $item->product ? route('api.products.image', ['product' => $item->product->id]) : null,
                            'imageUrl' => $item->product ? route('api.products.image', ['product' => $item->product->id]) : null,
                            'image_path' => $item->product?->image,
                        ],
                    ]),
                ]),
            ],
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $birthday = $user->birthday?->toDateString();

        if ($request->filled('birthday')) {
            try {
                $birthday = Carbon::createFromFormat(
                    '!j-F-Y',
                    sprintf(
                        '%d-%s-%d',
                        (int) $request->validated('birthday.day'),
                        (string) $request->validated('birthday.month'),
                        (int) $request->validated('birthday.year'),
                    ),
                )->toDateString();
            } catch (Throwable $throwable) {
                throw ValidationException::withMessages([
                    'birthday' => ['Invalid birthday. Please provide a valid date.'],
                ]);
            }
        }

        $user->forceFill([
            'name' => $request->validated('name'),
            'phone_number' => $request->validated('phone_number'),
            'birthday' => $birthday,
        ])->save();

        ActivityLog::log('user_profile_updated', "User {$user->email} updated profile.", $user->id);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'birthday' => $user->birthday?->toDateString(),
                'role' => $user->role,
                'is_active' => $user->is_active,
            ],
        ]);
    }

    public function googleLogin(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        $client = app(GoogleClient::class);
        $client->setClientId(config('services.google.client_id'));
        $payload = $client->verifyIdToken($request->input('id_token'));

        if (! $payload) {
            throw ValidationException::withMessages([
                'id_token' => ['Invalid Google token.'],
            ]);
        }

        $user = User::query()->where('google_id', $payload['sub'])->first()
            ?? User::query()->where('email', $payload['email'])->first();

        if ($user) {
            $user->update([
                'google_id' => $payload['sub'],
                'name' => $payload['name'] ?? $payload['email'],
            ]);
        } else {
            $user = User::query()->create([
                'google_id' => $payload['sub'],
                'name' => $payload['name'] ?? $payload['email'],
                'email' => $payload['email'],
                'password' => Hash::make(Str::random(32)),
                'role' => 'customer',
                'is_active' => true,
            ]);
        }

        abort_unless($user->is_active, 403, 'Account is deactivated.');

        $token = $user->createToken('google')->plainTextToken;

        ActivityLog::log('google_login', "User {$user->email} logged in via Google.", $user->id);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'birthday' => $user->birthday?->toDateString(),
            ],
            'token' => $token,
        ]);
    }

    private function issueOtpForUser(User $user): string
    {
        $otpCode = (string) random_int(100000, 999999);

        EmailVerificationOtp::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'otp_code_hash' => Hash::make($otpCode),
                'expires_at' => now()->addMinutes(5),
            ],
        );

        return $otpCode;
    }

    private function sendOtpEmail(User $user, string $otpCode): void
    {
        Mail::raw(
            "Your Nibiru verification code is: {$otpCode}. This code expires in 5 minutes.",
            function ($message) use ($user): void {
                $message->to($user->email)
                    ->subject('Nibiru Email Verification OTP');
            }
        );
    }
}
