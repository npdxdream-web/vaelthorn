<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoticeBoardPost extends Model
{
    protected $fillable = [
        'notice_board_thread_id',
        'created_by',
        'content',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(NoticeBoardThread::class, 'notice_board_thread_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
