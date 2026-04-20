<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'user_id',
        'status',
        'user_role',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Accessor: is_read maps to status for view compatibility.
     */
    public function getIsReadAttribute(): bool
    {
        return $this->status === 'read';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRoleLabelAttribute(): string
    {
        return match($this->user_role) {
            'admin'    => 'Admin',
            'doctor'   => 'Doctor',
            'patient'  => 'Patient',
            'pharmacy' => 'Pharmacy',
            'lab'      => 'Laboratory',
            'nurse'    => 'Nurse',
            default    => 'Guest',
        };
    }

    public function getRoleColorAttribute(): string
    {
        return match($this->user_role) {
            'admin'    => 'danger',
            'doctor'   => 'success',
            'patient'  => 'primary',
            'pharmacy' => 'warning',
            'lab'      => 'info',
            'nurse'    => 'secondary',
            default    => 'dark',
        };
    }
}
