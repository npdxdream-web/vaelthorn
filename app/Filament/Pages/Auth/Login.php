<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('ชื่อผู้ใช้')
                    ->required()
                    ->autocomplete()
                    ->extraInputAttributes(['tabindex' => 1]),
                $this->getPasswordFormComponent()
                    ->extraInputAttributes(['tabindex' => 2]),
                $this->getRememberFormComponent(),
            ]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'name'     => $data['name'],
            'password' => $data['password'],
        ];
    }
}