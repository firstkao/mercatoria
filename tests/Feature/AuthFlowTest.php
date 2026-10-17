<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Spatie\Honeypot\Honeypot;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('honeypot.amount_of_seconds', 0);
        app()->forgetInstance(Honeypot::class);
    }

    public function test_registration_creates_account_and_sends_email_verification(): void
    {
        Notification::fake();

        $response = $this->post(route('register'), [
            ...$this->validRegistrationData(),
            ...$this->honeypotFields(),
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $user = User::query()->where('email', 'collector@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->email_verified_at);
        $this->assertSame('628123456789', $user->no_hp);
        $this->assertTrue(Hash::check('Password123!', $user->password));
        $this->assertModelExists($user->crush);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'register_attempt',
        ]);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_verification_updates_user_and_crush_verification_dates(): void
    {
        $user = User::factory()->unverified()->create([
            'tgl_lahir' => '1990-01-01',
        ]);
        $crush = $user->crush()->create(['expires_at' => now()->addDays(30)]);
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(10),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())],
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect(route('dashboard'));
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertNotNull($crush->fresh()->verified_at);
        $this->assertSame(
            $user->fresh()->email_verified_at->toDateTimeString(),
            $crush->fresh()->verified_at->toDateTimeString(),
        );
    }

    public function test_registration_rejects_a_phone_number_already_in_normalized_format(): void
    {
        User::factory()->create(['no_hp' => '628123456789']);

        $response = $this->post(route('register'), [
            ...$this->validRegistrationData(),
            ...$this->honeypotFields(),
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('no_hp');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_requires_both_legal_consents(): void
    {
        $data = $this->validRegistrationData();
        unset($data['tnc'], $data['privacy']);

        $response = $this->post(route('register'), [
            ...$data,
            ...$this->honeypotFields(),
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['tnc', 'privacy']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_blocks_a_fourth_account_for_the_same_email_and_phone(): void
    {
        foreach (range(1, 3) as $attempt) {
            ActivityLog::create([
                'action' => 'register_attempt',
                'metadata' => [
                    'email' => 'collector@example.com',
                    'no_hp' => '628123456789',
                ],
            ]);
        }

        $response = $this->post(route('register'), [
            ...$this->validRegistrationData(),
            ...$this->honeypotFields(),
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseHas('spammers', [
            'email' => 'collector@example.com',
            'no_hp' => '628123456789',
            'status' => 'blocked',
        ]);
    }

    public function test_honeypot_rejects_registration_without_creating_an_account(): void
    {
        $honeypot = $this->honeypotFields();
        $honeypot[array_key_first($honeypot)] = 'filled by a bot';

        $response = $this->post(route('register'), [
            ...$this->validRegistrationData(),
            ...$honeypot,
        ]);

        $response->assertOk();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_login_authenticates_user_and_records_successful_attempt(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login'), [
            'email' => strtoupper($user->email),
            'password' => 'password',
            'remember' => '1',
            ...$this->honeypotFields(),
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login);
        $this->assertDatabaseHas('auth_logs', [
            'user_id' => $user->id,
            'action' => 'login_success',
        ]);
    }

    public function test_five_failed_login_attempts_trigger_a_fifteen_minute_lockout(): void
    {
        foreach (range(1, 5) as $attempt) {
            $response = $this->withHeader('referer', route('login'))->post(route('login'), [
                'email' => 'collector@example.com',
                'password' => 'invalid-password',
                ...$this->honeypotFields(),
            ]);

            $response->assertRedirect(route('login'));
        }

        $this->travel(61)->seconds();

        $response = $this->withHeader('referer', route('login'))->post(route('login'), [
            'email' => 'collector@example.com',
            'password' => 'invalid-password',
            ...$this->honeypotFields(),
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertStringStartsWith(
            'Terlalu banyak percobaan login.',
            session('errors')->first('email'),
        );
        $this->assertDatabaseHas('auth_logs', [
            'email_attempt' => 'collector@example.com',
            'action' => 'lockout',
            'reason' => 'too_many_attempts',
        ]);
    }

    private function validRegistrationData(): array
    {
        return [
            'name' => 'Mercatoria Collector',
            'email' => 'collector@example.com',
            'no_hp' => '08123456789',
            'tgl_lahir' => '1990-01-01',
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Kebayoran Baru',
            'alamat_lengkap' => 'Jl. Contoh Nomor 10',
            'kode_pos' => '12110',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'tnc' => '1',
            'privacy' => '1',
        ];
    }

    private function honeypotFields(): array
    {
        $response = $this->get(route('register'));
        $response->assertOk();
        $html = $response->getContent();

        preg_match('/<input\b[^>]*\bid="(my_name[^"]*)"[^>]*>/s', $html, $nameField);
        preg_match('/<input\b[^>]*\bname="valid_from"[^>]*\bvalue="([^"]+)"/s', $html, $timestampField);

        $this->assertArrayHasKey(1, $nameField);
        $this->assertArrayHasKey(1, $timestampField);

        return [
            $nameField[1] => '',
            'valid_from' => $timestampField[1],
        ];
    }
}
