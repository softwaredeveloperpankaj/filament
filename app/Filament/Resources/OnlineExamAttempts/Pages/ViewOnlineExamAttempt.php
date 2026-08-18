<?php

namespace App\Filament\Resources\OnlineExamAttempts\Pages;

use App\Filament\Resources\OnlineExamAttempts\OnlineExamAttemptResource;
use App\Filament\Resources\OnlineExamAttempts\Schemas\OnlineExamAttemptInfolist;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewOnlineExamAttempt extends ViewRecord
{
    protected static string $resource = OnlineExamAttemptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return OnlineExamAttemptInfolist::configure($schema);
    }
}
