<?php

use App\Mail\PasswordRecoveryCodeMail;
use App\Models\PasswordRecoveryCode;
use App\Models\User;
use App\Services\PasswordRecoveryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

function recoveryUser(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'email' => fake()->unique()->safeEmail(),
    ], $attributes));
}

it('completes password recovery by email and invalidates sessions', function () {
    Mail::fake();
    $user = recoveryUser(['remember_token' => 'remembered']);
    DB::table('sessions')->insert([
        'id' => 'active-session',
        'user_id' => $user->id,
        'payload' => 'payload',
        'last_activity' => now()->timestamp,
    ]);

    $service = app(PasswordRecoveryService::class);
    $recoveryId = $service->request('email', $user->email, '192.0.2.10');
    $mail = Mail::sent(PasswordRecoveryCodeMail::class)->first();

    expect($recoveryId)->toBeInt()
        ->and($mail)->toBeInstanceOf(PasswordRecoveryCodeMail::class)
        ->and($mail->code)->toMatch('/^\d{6}$/');

    expect($service->verify($recoveryId, $mail->code, '192.0.2.10'))->toBeTrue();
    expect($service->resetPassword($recoveryId, 'NuevaClave#123'))->toBeTrue();

    $user->refresh();
    expect(Hash::check('NuevaClave#123', $user->password))->toBeTrue()
        ->and($user->remember_token)->toBeNull()
        ->and(DB::table('sessions')->where('user_id', $user->id)->exists())->toBeFalse();
});

it('rejects sms recovery while the feature flag is disabled', function () {
    config()->set('auth.password_recovery.sms_enabled', false);

    expect(fn () => app(PasswordRecoveryService::class)->request(
        'sms',
        '+5493850000000',
        '192.0.2.11',
    ))->toThrow(RuntimeException::class, 'La recuperación por SMS está deshabilitada.');
});

it('rejects expired, incorrect and exhausted recovery codes', function () {
    $user = recoveryUser();
    $recovery = PasswordRecoveryCode::create([
        'user_id' => $user->id,
        'channel' => 'email',
        'identifier' => 'expired@example.test',
        'code_hash' => Hash::make('123456'),
        'attempts' => 0,
        'expires_at' => now()->subMinute(),
    ]);
    $service = app(PasswordRecoveryService::class);

    expect($service->verify($recovery->id, '123456', '192.0.2.12'))->toBeFalse();

    $recovery = PasswordRecoveryCode::create([
        'user_id' => $user->id,
        'channel' => 'email',
        'identifier' => 'attempts@example.test',
        'code_hash' => Hash::make('123456'),
        'attempts' => 0,
        'expires_at' => now()->addMinutes(10),
    ]);
    foreach (range(1, 5) as $attempt) {
        expect($service->verify($recovery->id, '000000', '192.0.2.'.(20 + $attempt)))->toBeFalse();
    }

    $recovery->refresh();
    expect($recovery->attempts)->toBe(5)
        ->and($service->verify($recovery->id, '123456', '192.0.2.30'))->toBeFalse();
});

it('does not reveal whether an email belongs to an account', function () {
    Mail::fake();
    $service = app(PasswordRecoveryService::class);

    $existing = $service->request('email', recoveryUser()->email, '192.0.2.40');
    $missing = $service->request('email', 'nobody@example.test', '192.0.2.41');

    expect($existing)->toBeInt()
        ->and($missing)->toBeNull()
        ->and(Mail::sent(PasswordRecoveryCodeMail::class))->toHaveCount(1);
});

it('does not send a code to an unverified email', function () {
    Mail::fake();
    $user = recoveryUser();
    $user->forceFill(['email_verified_at' => null])->save();

    expect(app(PasswordRecoveryService::class)->request('email', $user->email, '192.0.2.50'))->toBeNull()
        ->and(Mail::sent(PasswordRecoveryCodeMail::class))->toHaveCount(0);
});

it('verifies the latest code and normalizes surrounding whitespace', function () {
    Mail::fake();
    $user = recoveryUser();
    $service = app(PasswordRecoveryService::class);

    $firstId = $service->request('email', $user->email, '192.0.2.60');
    $firstMail = Mail::sent(PasswordRecoveryCodeMail::class)->first();
    $secondId = $service->request('email', $user->email, '192.0.2.61');
    $secondMail = Mail::sent(PasswordRecoveryCodeMail::class)->last();

    expect($firstId)->toBeInt()
        ->and($secondId)->toBeInt()
        ->and($firstMail->code)->not->toBe($secondMail->code)
        ->and($service->verify($secondId, ' '.$secondMail->code.' ', '192.0.2.61'))->toBeTrue();
});
