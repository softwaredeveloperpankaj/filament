<?php

namespace App\Filament\Resources\FormTemplates\Tables;

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
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


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
                    ->color('secondary')
                    ->sortable(),
                TextColumn::make('registration_serial')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('name')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Form Slug')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('type')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Form Status')
                    ->toggleable()
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        default => 'secondary',
                    }),
                IconColumn::make('is_active')
                    ->label('Is Template Active')
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
                Filter::make('is_active')
                    ->query(
                        fn (Builder $query): Builder => $query->where('is_active', true)
                    ),
                SelectFilter::make('rollno_generation_scope')
                    ->options(['class' => 'Class', 'section' => 'Section']),
                SelectFilter::make('status')
                    ->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'])
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

                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(FormTemplateExporter::class)
                        ->label('Export Templates'),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
