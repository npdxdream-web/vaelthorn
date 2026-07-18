<?php

namespace App\Filament\Pages;

use App\Models\AppSetting;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class OnboardingSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'ตั้งค่า Onboarding';
    protected static ?string $navigationGroup = 'ระบบ';
    protected static ?int    $navigationSort  = 10;
    protected static string  $view            = 'filament.pages.onboarding-settings';

    public ?int $onboarding_event_id = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->isAtLeastAdmin() ?? false;
    }

    public function mount(): void
    {
        $val = AppSetting::get('onboarding_event_id');
        $this->onboarding_event_id = $val ? (int) $val : null;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stage B — Event Onboarding')
                    ->description(
                        'เลือก Event ที่ใช้เป็น "Event Onboarding" สำหรับ Stage B ของผู้เล่นใหม่ (Level 0 → 1). '
                        . 'หาก null = Stage B ยังไม่เปิด ผู้เล่นที่ผ่าน Stage A จะรอและไม่สามารถ promote ได้'
                    )
                    ->schema([
                        Forms\Components\Select::make('onboarding_event_id')
                            ->label('Event Onboarding (Stage B)')
                            ->options(
                                Event::orderByDesc('created_at')
                                    ->pluck('title', 'id')
                                    ->toArray()
                            )
                            ->nullable()
                            ->searchable()
                            ->placeholder('— ยังไม่ได้เลือก (Stage B ปิดอยู่) —')
                            ->helperText(
                                'ผู้เล่น Level 0 ที่ผ่าน Stage A แล้วต้องเขียนโพสต์ใน Event นี้ '
                                . 'สะสม ' . config('leveling.stage_b_required_exp', 6) . ' EXP จึงจะ promote เป็น Level 1'
                            ),
                    ]),
            ])
            ->statePath('');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        AppSetting::set('onboarding_event_id', $data['onboarding_event_id'] ?: null);

        Notification::make()
            ->title('บันทึกการตั้งค่า Onboarding แล้ว')
            ->success()
            ->send();
    }
}
