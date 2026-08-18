<?php

namespace App\Filament\Resources\OnlineExamAttempts\Pages;

use App\Filament\Resources\OnlineExamAttempts\OnlineExamAttemptResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOnlineExamAttempt extends EditRecord
{
    protected static string $resource = OnlineExamAttemptResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         ViewAction::make(),
    //         DeleteAction::make(),
    //     ];
    // }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }    
}
