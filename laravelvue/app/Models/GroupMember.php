<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'user_id',
        'role'
    ];

    /**
     * Liên kết tới nhóm
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Liên kết tới người dùng
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function isAdmin()
{
    return $this->role === 'admin';
}


}

