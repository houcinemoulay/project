<?php

namespace Tests\Unit\Services;

use App\Models\Alert;
use App\Models\Patient;
use App\Models\User;
use App\Models\Vital;
use App\Services\AlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeVital(array $attributes = []): Vital
    {
        $patient = Patient::create([
            'name' => 'John Doe',
            'age' => 40,
            'gender' => 'male',
        ]);

        $nurse = User::factory()->create(['role' => 'nurse']);

        return Vital::create(array_merge([
            'patient_id' => $patient->id,
            'nurse_id' => $nurse->id,
            'blood_pressure_systolic' => 120,
            'blood_pressure_diastolic' => 80,
            'glucose_level' => 100,
            'heart_rate' => 70,
            'temperature' => 37.0,
            'oxygen_saturation' => 98,
        ], $attributes));
    }

    public function test_no_alerts_created_for_normal_vitals(): void
    {
        $alerts = AlertService::checkVitals($this->makeVital());

        $this->assertSame([], $alerts);
        $this->assertSame(0, Alert::count());
    }

    public function test_creates_alert_for_high_glucose(): void
    {
        $vital = $this->makeVital(['glucose_level' => 210.5]);

        $alerts = AlertService::checkVitals($vital);

        $this->assertCount(1, $alerts);
        $this->assertSame('high_glucose', $alerts[0]->type);
        $this->assertSame($vital->patient_id, $alerts[0]->patient_id);
        $this->assertStringContainsString('210.50 mg/dL', $alerts[0]->message);
        $this->assertStringContainsString('John Doe', $alerts[0]->message);
    }

    public function test_creates_alert_for_high_blood_pressure(): void
    {
        $alerts = AlertService::checkVitals($this->makeVital([
            'blood_pressure_systolic' => 160,
            'blood_pressure_diastolic' => 95,
        ]));

        $this->assertCount(1, $alerts);
        $this->assertSame('high_bp', $alerts[0]->type);
        $this->assertStringContainsString('160/95 mmHg', $alerts[0]->message);
    }

    public function test_creates_alert_for_high_heart_rate(): void
    {
        $alerts = AlertService::checkVitals($this->makeVital(['heart_rate' => 130]));

        $this->assertCount(1, $alerts);
        $this->assertSame('high_heart_rate', $alerts[0]->type);
        $this->assertStringContainsString('130 bpm', $alerts[0]->message);
    }

    public function test_creates_one_alert_per_triggered_rule(): void
    {
        $alerts = AlertService::checkVitals($this->makeVital([
            'glucose_level' => 200,
            'blood_pressure_systolic' => 150,
            'heart_rate' => 140,
        ]));

        $this->assertCount(3, $alerts);
        $this->assertSame(
            ['high_glucose', 'high_bp', 'high_heart_rate'],
            array_map(fn (Alert $alert) => $alert->type, $alerts)
        );
    }

    public function test_thresholds_are_exclusive(): void
    {
        $alerts = AlertService::checkVitals($this->makeVital([
            'glucose_level' => 180,
            'blood_pressure_systolic' => 140,
            'heart_rate' => 120,
        ]));

        $this->assertSame([], $alerts);
    }

    public function test_null_glucose_is_ignored(): void
    {
        $alerts = AlertService::checkVitals($this->makeVital(['glucose_level' => null]));

        $this->assertSame([], $alerts);
    }

    public function test_falls_back_to_unknown_patient_name(): void
    {
        $vital = $this->makeVital(['heart_rate' => 130]);
        $vital->setRelation('patient', null);

        $alerts = AlertService::checkVitals($vital);

        $this->assertStringContainsString('Unknown Patient', $alerts[0]->message);
    }

    /**
     * @dataProvider glucoseSeverityProvider
     */
    public function test_glucose_severity(?float $value, string $expected): void
    {
        $this->assertSame($expected, AlertService::getGlucoseSeverity($value));
    }

    public static function glucoseSeverityProvider(): array
    {
        return [
            'null' => [null, 'normal'],
            'normal' => [140.0, 'normal'],
            'warning' => [140.1, 'warning'],
            'warning upper bound' => [180.0, 'warning'],
            'critical' => [180.1, 'critical'],
        ];
    }

    /**
     * @dataProvider bpSeverityProvider
     */
    public function test_bp_severity(int $systolic, int $diastolic, string $expected): void
    {
        $this->assertSame($expected, AlertService::getBPSeverity($systolic, $diastolic));
    }

    public static function bpSeverityProvider(): array
    {
        return [
            'normal' => [120, 80, 'normal'],
            'warning systolic' => [121, 80, 'warning'],
            'warning diastolic' => [120, 81, 'warning'],
            'critical systolic' => [141, 80, 'critical'],
            'critical diastolic' => [120, 91, 'critical'],
        ];
    }

    /**
     * @dataProvider heartRateSeverityProvider
     */
    public function test_heart_rate_severity(int $value, string $expected): void
    {
        $this->assertSame($expected, AlertService::getHeartRateSeverity($value));
    }

    public static function heartRateSeverityProvider(): array
    {
        return [
            'normal' => [80, 'normal'],
            'warning high' => [101, 'warning'],
            'warning low' => [59, 'warning'],
            'critical high' => [121, 'critical'],
            'critical low' => [49, 'critical'],
        ];
    }

    /**
     * @dataProvider temperatureSeverityProvider
     */
    public function test_temperature_severity(float $value, string $expected): void
    {
        $this->assertSame($expected, AlertService::getTemperatureSeverity($value));
    }

    public static function temperatureSeverityProvider(): array
    {
        return [
            'normal' => [37.0, 'normal'],
            'warning high' => [37.6, 'warning'],
            'warning low' => [35.9, 'warning'],
            'critical high' => [38.6, 'critical'],
            'critical low' => [35.4, 'critical'],
        ];
    }

    /**
     * @dataProvider oxygenSeverityProvider
     */
    public function test_oxygen_severity(int $value, string $expected): void
    {
        $this->assertSame($expected, AlertService::getOxygenSeverity($value));
    }

    public static function oxygenSeverityProvider(): array
    {
        return [
            'normal' => [95, 'normal'],
            'warning' => [94, 'warning'],
            'critical' => [89, 'critical'],
        ];
    }

    public function test_severity_labels_and_badge_classes(): void
    {
        $this->assertSame('Critical', AlertService::getSeverityLabel('critical'));
        $this->assertSame('Warning', AlertService::getSeverityLabel('warning'));
        $this->assertSame('Normal', AlertService::getSeverityLabel('normal'));
        $this->assertSame('Normal', AlertService::getSeverityLabel('anything-else'));

        $this->assertSame('badge-critical', AlertService::getSeverityBadgeClass('critical'));
        $this->assertSame('badge-warning', AlertService::getSeverityBadgeClass('warning'));
        $this->assertSame('badge-success', AlertService::getSeverityBadgeClass('normal'));
        $this->assertSame('badge-success', AlertService::getSeverityBadgeClass('anything-else'));
    }
}
