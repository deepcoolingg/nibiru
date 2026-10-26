<?php

use App\Models\EmailVerificationOtp;
use App\Models\User;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

it('can register a new user', function () {
    Mail::fake();

    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone_number' => '+6281234567891',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'birthday' => [
            'day' => 12,
            'month' => 'March',
            'year' => 2000,
        ],
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email', 'phone_number', 'birthday', 'is_active'],
        ])
        ->assertJsonPath('user.is_active', false)
        ->assertJsonMissingPath('token');

    $this->assertDatabaseHas('email_verification_otps', [
        'user_id' => User::query()->where('email', 'test@example.com')->value('id'),
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'role' => 'customer',
        'is_active' => false,
    ]);
});

it('can verify otp and activate account', function () {
    $user = User::factory()->create([
        'email' => 'otp-user@example.com',
        'is_active' => false,
    ]);

    EmailVerificationOtp::query()->create([
        'user_id' => $user->id,
        'otp_code_hash' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(5),
    ]);

    $response = $this->postJson('/api/auth/verify-otp', [
        'email' => 'otp-user@example.com',
        'otp' => '123456',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email', 'phone_number', 'birthday', 'is_active'],
            'token',
        ])
        ->assertJsonPath('user.is_active', true);

    $this->assertDatabaseHas('users', [
        'email' => 'otp-user@example.com',
        'is_active' => true,
    ]);

    $this->assertDatabaseMissing('email_verification_otps', [
        'user_id' => $user->id,
    ]);
});

it('can resend otp for inactive account', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email' => 'resend-otp@example.com',
        'is_active' => false,
    ]);

    $response = $this->postJson('/api/auth/resend-otp', [
        'email' => 'resend-otp@example.com',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('message', 'A new OTP has been sent to your email.');

    $this->assertDatabaseHas('email_verification_otps', [
        'user_id' => $user->id,
    ]);

});

it('cannot register with duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/register', [
        'name' => 'Test',
        'email' => 'taken@example.com',
        'phone_number' => '+6281234567892',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'birthday' => [
            'day' => 1,
            'month' => 'January',
            'year' => 2001,
        ],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('cannot register with invalid data', function () {
    $this->postJson('/api/register', [
        'name' => '',
        'email' => 'not-an-email',
        'phone_number' => '',
        'password' => 'short',
        'birthday' => [
            'day' => '',
            'month' => '',
            'year' => '',
        ],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'phone_number', 'password', 'birthday.day', 'birthday.month', 'birthday.year']);
});

it('can login with valid credentials', function () {
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'user@example.com',
        'password' => 'password123',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'token',
        ]);
});

it('cannot login with wrong password', function () {
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => 'password123',
    ]);

    $this->postJson('/api/login', [
        'email' => 'user@example.com',
        'password' => 'wrongpassword',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('cannot login with inactive account', function () {
    User::factory()->create([
        'email' => 'inactive@example.com',
        'password' => 'password123',
        'is_active' => false,
    ]);

    $this->postJson('/api/login', [
        'email' => 'inactive@example.com',
        'password' => 'password123',
    ])->assertForbidden();
});

it('can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/logout')
        ->assertSuccessful();
});

it('can get authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/user')
        ->assertSuccessful()
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonPath('user.email', $user->email);
});

it('returns 401 for unauthenticated user endpoint', function () {
    $this->getJson('/api/user')
        ->assertUnauthorized();
});

it('can login with google id token', function () {
    $this->mock(GoogleClient::class, function ($mock) {
        $mock->shouldReceive('setClientId')->once()->andReturnSelf();
        $mock->shouldReceive('verifyIdToken')
            ->once()
            ->with('valid-google-token')
            ->andReturn([
                'sub' => 'google-123',
                'name' => 'Google User',
                'email' => 'google@example.com',
            ]);
    });

    $response = $this->postJson('/api/auth/google', [
        'id_token' => 'valid-google-token',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'token',
        ])
        ->assertJsonPath('user.email', 'google@example.com');

    $this->assertDatabaseHas('users', [
        'google_id' => 'google-123',
        'email' => 'google@example.com',
    ]);
});

it('returns existing user on repeat google login', function () {
    $existingUser = User::factory()->create([
        'google_id' => 'google-456',
        'email' => 'existing@example.com',
    ]);

    $this->mock(GoogleClient::class, function ($mock) {
        $mock->shouldReceive('setClientId')->once()->andReturnSelf();
        $mock->shouldReceive('verifyIdToken')
            ->once()
            ->with('repeat-token')
            ->andReturn([
                'sub' => 'google-456',
                'name' => 'Updated Name',
                'email' => 'existing@example.com',
            ]);
    });

    $response = $this->postJson('/api/auth/google', [
        'id_token' => 'repeat-token',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('user.id', $existingUser->id);

    $this->assertDatabaseCount('users', 1);
});

it('rejects invalid google token', function () {
    $this->mock(GoogleClient::class, function ($mock) {
        $mock->shouldReceive('setClientId')->once()->andReturnSelf();
        $mock->shouldReceive('verifyIdToken')
            ->once()
            ->with('invalid-token')
            ->andReturn(false);
    });

    $this->postJson('/api/auth/google', [
        'id_token' => 'invalid-token',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('id_token');
});

it('validates google id_token is required', function () {
    $this->postJson('/api/auth/google', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('id_token');
});
