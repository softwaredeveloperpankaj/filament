<?php

namespace App\Filament\Resources\Students\Tables;

use App\Filament\Exports\StudentExporter;
use App\Models\Student;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('registration_number')
                    ->label('Reg. No.')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('roll_no')
                    ->label('Roll No.')
                    ->placeholder('—')
                    ->toggleable(),

                // TextColumn::make('form_data.st_name')   // ← adjust to your field_key
                //     ->label('Student Name')
                //     ->searchable()
                //     ->toggleable(),


                TextColumn::make('class.name')
                    ->label('Class')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('section.name')
                    ->label('Section')
                    ->toggleable(),

                TextColumn::make('academic_year')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'danger'  => 'rejected',
                    ])
                    ->toggleable(),

                TextColumn::make('admission_date')
                    ->date()
                    ->placeholder('Pending')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ]),
            ])
            ->recordActionsColumnLabel('Actions')
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(StudentExporter::class)
                        ->before(function (Collection $records, ExportBulkAction $action) {
                            $students = Student::query()
                                ->whereIn('id', $records->all())
                                ->get();

                            if ($students->pluck('branch_id')->unique()->count() > 1) {
                                Notification::make()
                                    ->title('Export failed')
                                    ->body('All selected students must belong to the same branch to export.')
                                    ->danger()
                                    ->send();
                                $action->cancel();
                            }
                        }),

                    RestoreBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
