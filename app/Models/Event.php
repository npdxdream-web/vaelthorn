<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title', 'type', 'kingdom_id', 'created_by',
        'status', 'description', 'start_at', 'end_at', 'exp_reward',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    protected $appends = ['type_label', 'type_color', 'type_icon'];

    public function kingdom() { return $this->belongsTo(Kingdom::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function participants() { return $this->hasMany(EventParticipant::class); }
    public function requirements() { return $this->hasMany(EventRequirement::class); }
    public function rewards() { return $this->hasMany(Reward::class); }
    public function characters() { return $this->belongsToMany(Character::class, 'event_participants'); }
    public function threads() { return $this->hasMany(Thread::class); }

    /**
     * Presentation-only color/label mapping for the 4 existing event types.
     * Does not touch the `type` column, validation, or reward/EXP logic.
     */
    public static function typeMeta(): array
    {
        return [
            'flash'     => ['label' => 'Flash', 'color' => '#f59e0b', 'icon' => '⚡'],     // เหลืองทอง
            'location'  => ['label' => 'Location', 'color' => '#1d4ed8', 'icon' => '📍'],  // ฟ้าเข้ม
            'story_arc' => ['label' => 'Story Arc', 'color' => '#7c3aed', 'icon' => '📖'], // ม่วง
            'crisis'    => ['label' => 'Crisis', 'color' => '#b91c1c', 'icon' => '⚠'],     // แดงเข้ม
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeMeta()[$this->type]['label'] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getTypeColorAttribute(): string
    {
        return self::typeMeta()[$this->type]['color'] ?? '#c8a84b';
    }

    public function getTypeIconAttribute(): string
    {
        return self::typeMeta()[$this->type]['icon'] ?? '◆';
    }

    /**
     * Only meaningful for Flash events (short, time-boxed) with an end_at set —
     * other types run too long for a countdown to be useful in a picker list.
     */
    public function getFlashTimeRemainingLabelAttribute(): ?string
    {
        if ($this->type !== 'flash' || ! $this->end_at) {
            return null;
        }

        $minutesLeft = now()->diffInMinutes($this->end_at, false);

        if ($minutesLeft <= 0) {
            return 'หมดเวลาแล้ว';
        }

        if ($minutesLeft < 60) {
            return "เหลือ {$minutesLeft} นาที";
        }

        return 'เหลือ ' . intdiv($minutesLeft, 60) . ' ชม.';
    }
}
