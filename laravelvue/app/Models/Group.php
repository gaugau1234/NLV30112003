<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'created_by'
    ];

    /**
     * Người tạo nhóm (owner)
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Thành viên của nhóm
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members', 'group_id', 'user_id');
    }
}
