<?php

namespace Tests\Unit\Models;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_read_reflects_the_status_column(): void
    {
        $this->assertTrue((new ContactMessage(['status' => 'read']))->is_read);
        $this->assertFalse((new ContactMessage(['status' => 'new']))->is_read);
        $this->assertFalse((new ContactMessage)->is_read);
    }

    /**
     * @dataProvider rolePresentationProvider
     */
    public function test_role_label_and_color(?string $role, string $label, string $color): void
    {
        $message = new ContactMessage(['user_role' => $role]);

        $this->assertSame($label, $message->role_label);
        $this->assertSame($color, $message->role_color);
    }

    public static function rolePresentationProvider(): array
    {
        return [
            ['admin', 'Admin', 'danger'],
            ['doctor', 'Doctor', 'success'],
            ['patient', 'Patient', 'primary'],
            ['pharmacy', 'Pharmacy', 'warning'],
            ['lab', 'Laboratory', 'info'],
            ['nurse', 'Nurse', 'secondary'],
            ['unknown', 'Guest', 'dark'],
            [null, 'Guest', 'dark'],
        ];
    }

    public function test_belongs_to_the_user_who_sent_it(): void
    {
        $user = User::factory()->create(['role' => 'doctor']);

        $message = ContactMessage::create([
            'name' => $user->name,
            'email' => $user->email,
            'subject' => 'Question',
            'message' => 'Hello',
            'user_id' => $user->id,
            'user_role' => 'doctor',
            'status' => 'new',
        ]);

        $this->assertSame($user->id, $message->user->id);
        $this->assertFalse($message->is_read);
    }
}
