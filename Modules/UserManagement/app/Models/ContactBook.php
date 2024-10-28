<?php

namespace Modules\UserManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

// use Modules\UserManagement\Database\Factories\ContactBookFactory;

class ContactBook extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    use SoftDeletes;

    protected $table = 'mod_001_contacts_book';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'avatar',
        'phone_number',
        'meta_data',
        'last_updated'
    ];

    protected $casts = [
        'meta_data' => 'array',
        'last_updated' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function updateProfile(array $profileData): bool
    {
        $this->fill($profileData);
        $this->last_updated = now();
        return $this->save();
    }

    public function updateAvatar(string $avatarPath): bool
    {
        if ($this->avatar) {
            Storage::delete($this->avatar);
        }

        $this->avatar = $avatarPath;
        $this->last_updated = now();
        return $this->save();
    }

    public function getMetaData(?string $key = null)
    {
        if ($key === null) {
            return $this->meta_data;
        }

        return data_get($this->meta_data, $key);
    }

    public function setMetaData(string $key, $value): bool
    {
        $metaData = $this->meta_data ?? [];
        data_set($metaData, $key, $value);
        $this->meta_data = $metaData;
        $this->last_updated = now();
        return $this->save();
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}