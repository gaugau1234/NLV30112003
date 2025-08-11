<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'group_id',
        'assigned_to',
        'status',
        'due_date',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
