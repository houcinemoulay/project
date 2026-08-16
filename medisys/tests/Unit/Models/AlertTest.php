<?php

namespace Tests\Unit\Models;

use App\Models\Alert;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider presentationProvider
     */
    public function test_presentation_accessors(string $type, string $severity, string $label, string $badgeClass): void
    {
        $alert = new Alert(['type' => $type]);

        $this->assertSame($severity, $alert->severity);
        $this->assertSame($label, $alert->type_label);
        $this->assertSame($badgeClass, $alert->badge_class);
    }

    public static function presentationProvider(): array
    {
        return [
            ['high_glucose', 'warning', 'High Glucose', 'badge-warning'],
            ['high_bp', 'critical', 'High Blood Pressure', 'badge-critical'],
            ['high_heart_rate', 'warning', 'High Heart Rate', 'badge-warning'],
            ['something_else', 'warning', 'Alert', 'badge-warning'],
        ];
    }

    public function test_is_read_is_cast_to_boolean_and_defaults_to_false(): void
    {
        $alert = $this->createAlert();

        $this->assertFalse($alert->fresh()->is_read);

        $alert->update(['is_read' => 1]);

        $this->assertTrue($alert->fresh()->is_read);
    }

    public function test_unread_scope_only_returns_unread_alerts(): void
    {
        $unread = $this->createAlert();
        $this->createAlert(['is_read' => true]);

        $this->assertSame([$unread->id], Alert::unread()->pluck('id')->all());
    }

    public function test_for_patient_scope_filters_by_patient(): void
    {
        $alert = $this->createAlert();
        $this->createAlert(['patient_id' => $this->createPatient('Other')->id]);

        $this->assertSame([$alert->id], Alert::forPatient($alert->patient_id)->pluck('id')->all());
    }

    public function test_recent_scope_orders_by_newest_first(): void
    {
        $older = $this->createAlert();
        $older->forceFill(['created_at' => now()->subDay()])->save();
        $newer = $this->createAlert();

        $this->assertSame([$newer->id, $older->id], Alert::recent()->pluck('id')->all());
    }

    public function test_belongs_to_a_patient(): void
    {
        $alert = $this->createAlert();

        $this->assertSame('Jane Doe', $alert->patient->name);
    }

    private function createPatient(string $name = 'Jane Doe'): Patient
    {
        return Patient::create(['name' => $name, 'age' => 40, 'gender' => 'female']);
    }

    private function createAlert(array $attributes = []): Alert
    {
        return Alert::create(array_merge([
            'patient_id' => $attributes['patient_id'] ?? $this->createPatient()->id,
            'type' => 'high_bp',
            'message' => 'Blood pressure is elevated',
        ], $attributes));
    }
}
