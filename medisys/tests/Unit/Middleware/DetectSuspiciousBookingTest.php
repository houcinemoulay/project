<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\DetectSuspiciousBooking;
use App\Models\FraudAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class DetectSuspiciousBookingTest extends TestCase
{
    use RefreshDatabase;

    private function handle(array $input, string $ip = '10.0.0.1')
    {
        $request = Request::create('/appointments/book', 'POST', $input, [], [], [
            'REMOTE_ADDR' => $ip,
            'HTTP_USER_AGENT' => 'PHPUnit',
        ]);

        return (new DetectSuspiciousBooking)->handle($request, fn () => response('passed'));
    }

    private function seedAttempt(array $attributes): FraudAttempt
    {
        return FraudAttempt::create(array_merge([
            'ip_address' => '10.0.0.1',
            'reason' => 'rate_limit',
        ], $attributes));
    }

    public function test_allows_a_legitimate_booking(): void
    {
        $response = $this->handle(['email' => 'jane@example.com', 'phone' => '0612345678']);

        $this->assertSame('passed', $response->getContent());
        $this->assertSame(0, FraudAttempt::count());
    }

    public function test_blocks_and_logs_honeypot_submissions(): void
    {
        try {
            $this->handle(['email' => 'bot@example.com', 'phone' => '0612345678', 'website' => 'spam.example']);
            $this->fail('Expected the request to be aborted.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
            $this->assertSame('Suspicious activity detected', $exception->getMessage());
        }

        $attempt = FraudAttempt::sole();
        $this->assertSame('honeypot', $attempt->reason);
        $this->assertSame('10.0.0.1', $attempt->ip_address);
        $this->assertSame('bot@example.com', $attempt->email);
        $this->assertSame('PHPUnit', $attempt->user_agent);
        $this->assertSame('spam.example', $attempt->payload['website']);
    }

    public function test_blocks_confirm_email_honeypot(): void
    {
        $this->expectException(HttpException::class);

        $this->handle(['confirm_email' => 'bot@example.com']);
    }

    public function test_rate_limits_after_five_recent_attempts_from_same_ip(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->seedAttempt(['ip_address' => '10.0.0.9']);
        }

        try {
            $this->handle(['email' => 'jane@example.com'], '10.0.0.9');
            $this->fail('Expected the request to be aborted.');
        } catch (HttpException $exception) {
            $this->assertSame(429, $exception->getStatusCode());
            $this->assertSame('Too many booking attempts. Please try again later.', $exception->getMessage());
        }

        $this->assertSame('rate_limit', FraudAttempt::latest('id')->first()->reason);
    }

    public function test_old_attempts_do_not_count_towards_the_rate_limit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->seedAttempt(['ip_address' => '10.0.0.9'])
                ->forceFill(['created_at' => now()->subMinutes(5)])
                ->save();
        }

        $this->assertSame('passed', $this->handle([], '10.0.0.9')->getContent());
    }

    public function test_blocks_duplicate_email_within_five_minutes(): void
    {
        $this->seedAttempt(['email' => 'jane@example.com', 'reason' => 'duplicate']);

        try {
            $this->handle(['email' => 'jane@example.com']);
            $this->fail('Expected the request to be aborted.');
        } catch (HttpException $exception) {
            $this->assertSame(429, $exception->getStatusCode());
            $this->assertSame('Duplicate booking attempt detected.', $exception->getMessage());
        }

        $this->assertSame(2, FraudAttempt::where('reason', 'duplicate')->count());
    }

    public function test_blocks_duplicate_phone_within_five_minutes(): void
    {
        $this->seedAttempt(['phone' => '0612345678', 'reason' => 'duplicate']);

        try {
            $this->handle(['phone' => '0612345678']);
            $this->fail('Expected the request to be aborted.');
        } catch (HttpException $exception) {
            $this->assertSame(429, $exception->getStatusCode());
        }
    }

    public function test_duplicate_check_ignores_older_attempts(): void
    {
        $this->seedAttempt(['email' => 'jane@example.com', 'reason' => 'duplicate'])
            ->forceFill(['created_at' => now()->subMinutes(10)])
            ->save();

        $this->assertSame('passed', $this->handle(['email' => 'jane@example.com'])->getContent());
    }
}
