<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouncilLetter extends Model
{
    protected $fillable = [
        'character_id',
        'subject',
        'body',
        'status',
        'admin_reply',
        'replied_by',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    public function character()
    {
        return $this->belongsTo(Character::class);
    }

    public function repliedBy()
    {
        return $this->belongsTo(User::class, 'replied_by');
    }
}
