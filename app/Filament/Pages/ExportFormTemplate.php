<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExportFormTemplate extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder-arrow-down';

    protected static ?string $navigationLabel = 'Exported Form Files';

    protected static ?string $title = 'Exported Form Files';

    protected static ?string $slug = 'exported-form-files';

    protected string $view = 'filament.pages.export-form-template';

    protected function getViewData(): array
    {
        $disk = Storage::disk('local');

        return [
            'files' => collect($disk->exists('form_exports') ? $disk->files('form_exports') : [])
                ->filter(fn ($file) => Str::startsWith(basename($file), 'bulk_forms_'))
                ->map(fn ($file) => [
                    'path' => encrypt($file),
                    'name' => basename($file),
                    'size' => round($disk->size($file) / 1024, 2),
                    'last_modified' => $disk->lastModified($file),
                ])
                ->sortByDesc('last_modified')
                ->values(),
        ];
    }

    public function downloadFile(string $file): mixed
    {
        $path = decrypt($file);
        
        if (! str_starts_with($path, 'form_exports/')) {
            Notification::make()
                ->title('Invalid file path')
                ->danger()
                ->send();

            return null;
        }

        if (! Storage::disk('local')->exists($path)) {
            Notification::make()
                ->title('File not found')
                ->danger()
                ->send();

            return null;
        }

        return Storage::disk('local')->download($path, basename($path));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to Templates')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => route('filament.admin.resources.form-templates.index')),
        ];
    }    

}