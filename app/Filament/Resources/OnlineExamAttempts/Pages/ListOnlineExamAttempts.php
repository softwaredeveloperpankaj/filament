<?php

namespace App\Filament\Resources\OnlineExamAttempts\Pages;

use App\Filament\Resources\OnlineExamAttempts\OnlineExamAttemptResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOnlineExamAttempts extends ListRecords
{
    protected static string $resource = OnlineExamAttemptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //CreateAction::make(),
        ];
    }
}
