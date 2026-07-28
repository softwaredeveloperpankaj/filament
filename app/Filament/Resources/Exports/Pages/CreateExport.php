<?php

namespace App\Filament\Resources\Exports\Pages;

use App\Filament\Resources\Exports\ExportResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExport extends CreateRecord
{
    protected static string $resource = ExportResource::class;
}
