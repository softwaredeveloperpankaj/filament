<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'employee_id', 'branch_id'])]
#[Hidden(['password', 'remember_token'])]

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function teacherProfile(): HasOne
    {
        return $this->hasOne(TeacherProfile::class);
    }

    public function branch()
    {
        return $this->hasMany(Branch::class, 'user_id');
    }

    public static function generateEmployeeId(string $roleSlug, int $branchId): string
    {
        // Format: TEACHER-2-0001, PRINCIPAL-1-0003, CLASS-TEACHER-1-0002
        $prefix = strtoupper(str_replace(' ', '-', $roleSlug));

        $count = self::where('branch_id', $branchId)
            ->whereHas('roles', fn ($q) => $q->where('name', $roleSlug))
            ->whereNotNull('employee_id')
            ->where('employee_id', 'like', "{$prefix}-{$branchId}-%")
            ->count();

        return sprintf('%s-%d-%04d', $prefix, $branchId, $count + 1);
    }    
}
