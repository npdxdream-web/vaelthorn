<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ThreadCategory: string implements HasLabel, HasColor
{
    case Announcement = 'announcement';
    case SystemUpdate = 'system_update';
    case Event = 'event';
    case RulePolicy = 'rule_policy';
    case Guide = 'guide';
    case Faq = 'faq';
    case BugReport = 'bug_report';
    case GeneralDiscussion = 'general_discussion';

    public function getLabel(): string
    {
        return match ($this) {
            self::Announcement => 'ประกาศ',
            self::SystemUpdate => 'อัปเดตระบบ',
            self::Event => 'กิจกรรม',
            self::RulePolicy => 'กฎ/นโยบาย',
            self::Guide => 'คู่มือ',
            self::Faq => 'คำถามที่พบบ่อย',
            self::BugReport => 'แจ้งบั๊ก',
            self::GeneralDiscussion => 'พูดคุยทั่วไป',
        };
    }

    /**
     * Native Filament format (RGB-per-shade array) — used by admin table/form badges.
     */
    public function getColor(): array
    {
        return match ($this) {
            self::Announcement => Color::Cyan,
            self::SystemUpdate => Color::Slate,
            self::Event => Color::Green,
            self::RulePolicy => Color::Stone,
            self::Guide => Color::Lime,
            self::Faq => Color::Pink,
            self::BugReport => Color::Orange,
            self::GeneralDiscussion => Color::Fuchsia,
        };
    }

    /**
     * Dark background hex, for the frontend card-corner badge — derived from
     * getColor() so the admin badge and the frontend badge always share one ramp.
     */
    public function bgHex(): string
    {
        return self::rgbToHex($this->getColor()[950]);
    }

    /**
     * Light text hex from the same ramp as bgHex().
     */
    public function textHex(): string
    {
        return self::rgbToHex($this->getColor()[300]);
    }

    private static function rgbToHex(string $rgb): string
    {
        [$r, $g, $b] = array_map('trim', explode(',', $rgb));

        return sprintf('#%02x%02x%02x', (int) $r, (int) $g, (int) $b);
    }
}
