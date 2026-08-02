<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticeBoard extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function threads()
    {
        return $this->hasMany(NoticeBoardThread::class);
    }
}
