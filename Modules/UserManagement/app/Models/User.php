<?php

namespace Modules\UserManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\UserManagement\Services\HashingService;
// use Modules\UserManagement\Database\Factories\UserFactory;

class User extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    use SoftDeletes;

    protected $table = 'mod_001_users';

    protected $fillable = [
        'login_name',
        'password_hash',
        'password_salt',
        'hash_algorithm_id',
        'email_address',
        'confirmation_token',
        'token_generation_time',
        'email_validation_status_id',
        'password_recovery_token',
        'recovery_token_time',
        'no_failed_attempts',
        'user_status_id'
    ];

    protected $hidden = [
        'password_hash',
        'password_salt',
        'confirmation_token',
        'password_recovery_token'
    ];

    protected $casts = [
        'token_generation_time' => 'datetime',
        'recovery_token_time' => 'datetime',
        'no_failed_attempts' => 'integer',
    ];

    public function profile() 
    {
        return $this->hasOne(ContactBook::class, 'user_id', 'user_id');
    }

    public function devices()
    {
        return $this->hasMany(UserDevice::class, 'user_id', 'user_id');
    }

    public function status()
    {
        return $this->belongsTo(UserStatusType::class, 'user_status_id', 'user_status_id');
    }

    public function hashingAlgorithm()
    {
        return $this->belongsTo(HashingAlgorithm::class, 'hash_algorithm_id', 'hash_algorithm_id');
    }

    public function emailStatus()
    {
        return $this->belongsTo(EmailValidation::class, 'email_validation_status_id', 'email_validation_status_id');
    }

    public function authenticate(string $password): bool
    {
        if ($this->isBlocked() || !$this->isActive()) {
            return false;
        }

        $hashingService = app(HashingService::class);
        $isValid = $hashingService->verify(
            $password,
            $this->password_hash,
            $this->password_salt,
            $this->hashingAlgorithm
        );

        if (!$isValid) {
            $this->incrementFailedAttempts();
            return false;
        }

        $this->resetFailedAttempts();
        return true;
    }

    public function isBlocked(): bool
    {
        return $this->no_failed_attempts >= config('user-management.max_login_attempts', 5)
            || $this->status->status_name === 'Locked';
    }

    public function isActive(): bool
    {
        return $this->status->status_name === 'Active' 
            && $this->status->is_active;
    }

    public function incrementFailedAttempts(): void
    {
        $this->increment('no_failed_attempts');
        
        if ($this->no_failed_attempts >= config('user-management.max_login_attempts', 5)) {
            $this->updateStatus(
                UserStatusType::where('status_name', 'Locked')->first()->user_status_id
            );
        }
        
        $this->save();
    }

    public function resetFailedAttempts(): void
    {
        $this->no_failed_attempts = 0;
        $this->save();
    }

    public function updateStatus(int $statusId): void
    {
        $this->user_status_id = $statusId;
        $this->save();
    }

}
