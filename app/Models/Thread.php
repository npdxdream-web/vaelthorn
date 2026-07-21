<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Thread extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'city_id',
        'event_id',
        'created_by',
        'title',
        'location_label',
        'status',
        'moderation_message',
        'archived_at',
        'exp_override',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    protected $appends = ['status_label', 'status_color'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function author()
    {
        return $this->belongsTo(Character::class, 'created_by', 'user_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'approved', 'open' => 'Live',
            'pending'          => 'รออนุมัติ',
            'draft'            => 'ฉบับร่าง',
            'request_edit'     => 'ถูกขอแก้ไข',
            'rejected'         => 'ถูกปฏิเสธ',
            'locked'           => 'ปิดแล้ว',
            'archived'         => 'เก็บถาวร',
            default            => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'approved', 'open' => 'text-emerald-400 bg-emerald-950/30 border-emerald-400/20',
            'pending'          => 'text-amber-300 bg-amber-950/30 border-amber-400/20',
            'draft'            => 'text-slate-400 bg-slate-950/30 border-slate-400/20',
            'request_edit'     => 'text-orange-400 bg-orange-950/30 border-orange-400/20',
            'rejected'         => 'text-rose-400 bg-rose-950/30 border-rose-400/20',
            'locked'           => 'text-slate-300 bg-slate-800/30 border-slate-500/20',
            'archived'         => 'text-indigo-300 bg-indigo-950/30 border-indigo-400/20',
            default            => 'text-gray-300 bg-gray-950/30 border-gray-400/20',
        };
    }

    public function isPubliclyVisible(): bool
    {
        return in_array($this->status, ['approved', 'open', 'locked', 'archived'], true);
    }

    public function isLive(): bool
    {
        return in_array($this->status, ['approved', 'open'], true);
    }

    public function approvedPostsCount(): int
    {
        return $this->posts()->where('status', 'approved')->count();
    }
}