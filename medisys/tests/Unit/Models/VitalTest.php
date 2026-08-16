<?php

namespace Tests\Unit\Models;

use App\Models\Patient;
use App\Models\User;
use App\Models\Vital;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VitalTest extends TestCase
{
    use RefreshDatabase;

    public function test_blood_pressure_accessor_combines_both_readings(): void
    {
        $vital = new Vital([
            'blood_pressure_systolic' => 130,
            'blood_pressure_diastolic' => 85,
        ]);

        $this->assertSame('130/85', $vital->blood_pressure);
    }

    public function test_numeric_values_are_cast_with_fixed_precision(): void
    {
        $vital = $this->createVital(['glucose_level' => 99.456, 'temperature' => 37.25, 'weight' => 70.5])->fresh();

        $this->assertSame('99.46', $vital->glucose_level);
        $this->assertSame('37.3', $vital->temperature);
        $this->assertSame('70.50', $vital->weight);
    }

    public function test_for_patient_scope_filters_by_patient(): void
    {
        $vital = $this->createVital();
        $this->createVital(['patient_id' => $this->createPatient('Other')->id]);

        $this->assertSame([$vital->id], Vital::forPatient($vital->patient_id)->pluck('id')->all());
    }

    public function test_latest_scope_orders_by_newest_first(): void
    {
        $older = $this->createVital();
        $older->forceFill(['created_at' => now()->subDay()])->save();
        $newer = $this->createVital();

        $this->assertSame([$newer->id, $older->id], Vital::latest()->pluck('id')->all());
    }

    public function test_relations_point_to_patient_and_recording_nurse(): void
    {
        $vital = $this->createVital();

        $this->assertSame('Jane Doe', $vital->patient->name);
        $this->assertSame('nurse', $vital->nurse->role);
    }

    private function createPatient(string $name = 'Jane Doe'): Patient
    {
        return Patient::create(['name' => $name, 'age' => 40, 'gender' => 'female']);
    }

    private function createVital(array $attributes = []): Vital
    {
        return Vital::create(array_merge([
            'patient_id' => $attributes['patient_id'] ?? $this->createPatient()->id,
            'nurse_id' => User::factory()->create(['role' => 'nurse'])->id,
            'blood_pressure_systolic' => 120,
            'blood_pressure_diastolic' => 80,
            'glucose_level' => 100,
            'heart_rate' => 70,
            'temperature' => 37.0,
            'oxygen_saturation' => 98,
        ], $attributes));
    }
}
