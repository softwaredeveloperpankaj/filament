<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
            Action::make('changePassword')
                ->label('Change Password')
                ->icon('heroicon-m-key')
                ->color('warning')
                // 1. Define the form fields that will appear inside the modal
                ->schema([
                    TextInput::make('new_password')
                        ->label('New Password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->rule(Password::default()), // Applies Laravel's default security rules

                    TextInput::make('new_password_confirmation')
                        ->label('Confirm New Password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->same('new_password'), // Ensures both inputs match
                ])
                // 2. Process the data once the user clicks "Save" inside the modal
                ->action(function (array $data): void {
                    $this->record->update([
                        'password' => Hash::make($data['new_password']),
                    ]);

                    Notification::make()
                        ->title('Password updated successfully')
                        ->success()
                        ->send();
                }),
            
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
