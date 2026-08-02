<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NoticeBoardThread extends Model
{
    protected $fillable = [
        'notice_board_id',
        'created_by',
        'title',
    ];

    public function noticeBoard(): BelongsTo
    {
        return $this->belongsTo(NoticeBoard::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(NoticeBoardPost::class);
    }
}
