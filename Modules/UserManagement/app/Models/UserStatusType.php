<?php

namespace Modules\UserManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\UserManagement\Database\Factories\UserStatusTypeFactory;

class UserStatusType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): UserStatusTypeFactory
    // {
    //     // return UserStatusTypeFactory::new();
    // }
}
