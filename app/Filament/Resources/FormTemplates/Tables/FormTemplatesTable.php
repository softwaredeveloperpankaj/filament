<?php

namespace App\Filament\Resources\FormTemplates\Tables;

use App\Filament\Actions\Bulk\BulkExportFormsAction;
use App\Filament\Actions\Bulk\BulkImportFormAction;
use App\Filament\Actions\FormTemplateExportAction;
use App\Filament\Actions\FormTemplateImportAction;
use App\Filament\Exports\FormTemplateExporter;
use App\Filament\Imports\FormTemplateImporter;
use App\Filament\Pages\FormBuilder;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FormTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->persistColumnsInSession()
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->searchable()
                    ->numeric(),
                TextColumn::make('branch.name')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                // TextColumn::make('active_version_id')
                //     ->numeric()
                //     ->sortable(),
                TextColumn::make('user.name')
                    ->toggleable()
                    ->badge()
                    ->sortable(),
                TextColumn::make('registration_serial')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('name')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('slug')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('type')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('status')
                    ->toggleable()
                    ->badge(),
                IconColumn::make('is_active')
                    ->toggleable()
                    ->boolean(),
                TextColumn::make('form_layout')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('rollno_generation_scope')
                    ->toggleable()
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    Action::make('builder')
                        ->label('Open Builder')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->color('warning')
                        ->url(fn ($record): string => FormBuilder::getUrl([
                            'template' => $record->id,
                        ])),

                    EditAction::make(),
                    DeleteAction::make(),
                    FormTemplateExportAction::exportForm(),
                    FormTemplateImportAction::importForm(),
                ]),
            ])
            ->recordActionsColumnLabel('Actions')
            ->toolbarActions([
                ImportAction::make()
                    ->importer(FormTemplateImporter::class)
                    ->label('Import Templates'),

                ExportAction::make()
                    ->exporter(FormTemplateExporter::class)
                    ->label('Export All Templates'),
                BulkExportFormsAction::make(),
                BulkImportFormAction::make(),
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(FormTemplateExporter::class)
                        ->label('Export Selected Templates'),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
