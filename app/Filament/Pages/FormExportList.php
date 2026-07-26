<?php

namespace App\Filament\Pages;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;

class FormExportList extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;
    use RestrictsFileUploadsToSchemaComponents;

    public function table(Table $table): Table
    {
        return $table
            ->records(fn () => collect(
                Storage::disk('local')->exists('form_exports')
                    ? Storage::disk('local')->files('form_exports')
                    : []
            )
                ->filter(fn (string $file) => Str::startsWith(basename($file), 'bulk_forms_'))
                ->map(fn (string $file) => (object) [
                    'path' => $file,
                    'name' => basename($file),
                    'size' => Storage::disk('local')->size($file),
                    'last_modified' => Storage::disk('local')->lastModified($file),
                ])
                ->sortByDesc('last_modified')
                ->values())
            ->columns([
                TextColumn::make('name')
                    ->label('File Name'),

                TextColumn::make('size')
                    ->label('Size')
                    ->formatStateUsing(fn ($state) => number_format($state / 1024, 2) . ' KB'),

                TextColumn::make('last_modified')
                    ->label('Last Modified')
                    ->dateTime('d M Y, h:i A'),
            ]);
    }
}
