<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),

            Action::make('printAdmissionForm')
                ->label('Print Admission Form')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->modalHeading('Admission Form Preview')
                ->modalWidth(Width::FourExtraLarge) // '4xl' — wide enough for a form
                ->modalContent(function ($record) {
                    $template_layout_name = $record->formTemplate?->form_layout ?? 'default';
                    $view_path = "filament.print-admission-form.{$template_layout_name}";
                    return view($view_path, [
                        'student' => $record
                    ]);
                })
                ->modalFooterActions([
                    Action::make('print')
                        ->label('Print')
                        ->icon('heroicon-o-printer')
                        ->color('primary')
                        ->action(fn () => null) // actual print triggered by JS
                        ->extraAttributes([
                            'onclick' => 'window.print()',
                        ]),
                    Action::make('cancel')
                        ->label('Close')
                        ->color('gray')
                        ->close(),
                ]),

            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
