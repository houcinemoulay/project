<?php

namespace Tests\Unit\Services;

use App\Services\BookingFraudService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use ReflectionMethod;
use Tests\TestCase;

class BookingFraudServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingFraudService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->service = new BookingFraudService;
    }

    private function invokePrivate(string $method, array $arguments): mixed
    {
        $reflection = new ReflectionMethod($this->service, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($this->service, $arguments);
    }

    public function test_first_booking_from_ip_is_not_suspicious(): void
    {
        $result = $this->service->detectFraud('10.0.0.1', '', '');

        $this->assertFalse($result['is_suspicious']);
        $this->assertSame(0, $result['fraud_score']);
        $this->assertSame([], $result['indicators']);
        $this->assertSame('minimal', $result['risk_level']);
        $this->assertSame('No action needed', $result['recommendation']);
    }

    public function test_flags_high_frequency_bookings_from_same_ip(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->assertFalse($this->service->detectFraud('10.0.0.2', '', '')['is_suspicious']);
        }

        $result = $this->service->detectFraud('10.0.0.2', '', '');

        $this->assertTrue($result['is_suspicious']);
        $this->assertSame(40, $result['fraud_score']);
        $this->assertSame('high_frequency', $result['indicators'][0]['type']);
        $this->assertSame('high', $result['risk_level']);
        $this->assertSame('Flag for manual review and consider temporary suspension', $result['recommendation']);
    }

    public function test_booking_counts_are_tracked_per_ip(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->service->detectFraud('10.0.0.3', '', '');
        }

        $this->assertFalse($this->service->detectFraud('10.0.0.4', '', '')['is_suspicious']);
        $this->assertSame(6, Cache::get('booking_count_10.0.0.3'));
        $this->assertSame(1, Cache::get('booking_count_10.0.0.4'));
    }

    /**
     * @dataProvider riskLevelProvider
     */
    public function test_risk_level_thresholds(int $score, string $expected): void
    {
        $this->assertSame($expected, $this->invokePrivate('getRiskLevel', [$score]));
    }

    public static function riskLevelProvider(): array
    {
        return [
            [0, 'minimal'],
            [9, 'minimal'],
            [10, 'low'],
            [20, 'medium'],
            [30, 'high'],
            [49, 'high'],
            [50, 'critical'],
        ];
    }

    /**
     * @dataProvider recommendationProvider
     */
    public function test_recommendation_thresholds(int $score, string $expected): void
    {
        $this->assertSame($expected, $this->invokePrivate('getRecommendation', [$score]));
    }

    public static function recommendationProvider(): array
    {
        return [
            [0, 'No action needed'],
            [10, 'Standard verification process recommended'],
            [20, 'Monitor closely and consider additional verification'],
            [30, 'Flag for manual review and consider temporary suspension'],
            [50, 'Block booking immediately and require manual verification'],
        ];
    }

    public function test_detects_temporary_email_services(): void
    {
        $patterns = $this->invokePrivate('checkSuspiciousPatterns', ['visitor@mailinator.com', '']);

        $this->assertSame(['Temporary email service detected: mailinator'], $patterns);
    }

    public function test_detects_sequential_email_pattern(): void
    {
        $patterns = $this->invokePrivate('checkSuspiciousPatterns', ['john123@example.com', '']);

        $this->assertSame(['Sequential email pattern detected'], $patterns);
    }

    public function test_detects_too_short_phone_number(): void
    {
        $patterns = $this->invokePrivate('checkSuspiciousPatterns', ['jane.doe@example.com', '12345']);

        $this->assertSame(['Invalid phone format'], $patterns);
    }

    public function test_detects_repeated_phone_number(): void
    {
        $patterns = $this->invokePrivate('checkSuspiciousPatterns', ['jane.doe@example.com', '11111111']);

        $this->assertContains('Repeated phone number pattern', $patterns);
    }

    public function test_no_patterns_for_clean_contact_details(): void
    {
        $patterns = $this->invokePrivate('checkSuspiciousPatterns', ['jane.doe@example.com', '']);

        $this->assertSame([], $patterns);
    }

    public function test_phone_is_only_inspected_when_an_email_is_present(): void
    {
        $patterns = $this->invokePrivate('checkSuspiciousPatterns', ['', '12345']);

        $this->assertSame([], $patterns);
    }
}
