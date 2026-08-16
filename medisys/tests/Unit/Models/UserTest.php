<?php

namespace Tests\Unit\Models;

use App\Models\Doctor;
use App\Models\Laboratory;
use App\Models\Nurse;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider roleProvider
     */
    public function test_role_helpers_only_match_their_own_role(string $role, string $trueHelper): void
    {
        $user = new User(['role' => $role]);
        $helpers = ['isAdmin', 'isDoctor', 'isPharmacy', 'isLab', 'isNurse'];

        foreach ($helpers as $helper) {
            $this->assertSame($helper === $trueHelper, $user->{$helper}(), $helper);
        }
    }

    public static function roleProvider(): array
    {
        return [
            ['admin', 'isAdmin'],
            ['doctor', 'isDoctor'],
            ['pharmacy', 'isPharmacy'],
            ['lab', 'isLab'],
            ['nurse', 'isNurse'],
            ['patient', 'none'],
        ];
    }

    public function test_password_is_hashed_and_hidden(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'password' => 'secret-password']);

        $this->assertNotSame('secret-password', $user->password);
        $this->assertTrue(Hash::check('secret-password', $user->password));
        $this->assertArrayNotHasKey('password', $user->toArray());
        $this->assertArrayNotHasKey('remember_token', $user->toArray());
    }

    public function test_profile_relations(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create(['user_id' => $doctorUser->id, 'specialty' => 'Cardiology']);

        $pharmacyUser = User::factory()->create(['role' => 'pharmacy']);
        $pharmacy = Pharmacy::create([
            'user_id' => $pharmacyUser->id,
            'name' => 'Central Pharmacy',
            'address' => '2 Main Street',
        ]);

        $labUser = User::factory()->create(['role' => 'lab']);
        $laboratory = Laboratory::create([
            'user_id' => $labUser->id,
            'name' => 'Central Lab',
            'address' => '1 Main Street',
        ]);

        $nurseUser = User::factory()->create(['role' => 'nurse']);
        $nurse = Nurse::create(['user_id' => $nurseUser->id]);

        $this->assertSame($doctor->id, $doctorUser->doctor->id);
        $this->assertSame($pharmacy->id, $pharmacyUser->pharmacy->id);
        $this->assertSame($laboratory->id, $labUser->laboratory->id);
        $this->assertSame($nurse->id, $nurseUser->nurse->id);
        $this->assertNull($doctorUser->pharmacy);
    }
}
