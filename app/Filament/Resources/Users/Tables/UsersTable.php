<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Exports\TeacherProfileExporter;
use App\Filament\Exports\UserExporter;
use App\Filament\Imports\TeacherProfileImporter;
use App\Filament\Imports\UserImporter;
use App\Models\User;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Full name')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('employee_id')
                    ->label('Employee ID')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('roles')
                    ->label('Role')
                    ->getStateUsing(fn ($record) => $record->getRoleNames()->first() ?? 'User')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'success',
                        'teacher' => 'info',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => str($state)->replace('_', ' ')->title())
                    ->toggleable(),
                // TextColumn::make('email_verified_at')
                //     ->dateTime()
                //     ->sortable(),
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
                SelectFilter::make('role')
                    ->relationship('roles', 'name')
                    ->label('Role'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->recordActionsColumnLabel('Actions')
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make('bulk_export_user')
                        ->label('Export users')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->exporter(UserExporter::class),
                    ExportBulkAction::make('bulk_export_teacher')
                        ->label('Export teachers')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->exporter(TeacherProfileExporter::class)
                        ->before(function (ExportBulkAction $action) {
                            $selectedIds = collect($action->getSelectedRecords());

                            $users = User::query()
                                ->whereIn('id', $selectedIds)
                                ->with('teacherProfile')
                                ->get();

                            $invalidUsers = $users->filter(
                                fn (User $user) =>
                                    ! $user->hasRole('teacher') ||
                                    ! $user->teacherProfile
                            );

                            if ($invalidUsers->isNotEmpty()) {
                                Notification::make()
                                    ->danger()
                                    ->title('Invalid selection')
                                    ->body('Please select only users who have the Teacher role.')
                                    ->send();

                                $action->halt();
                            }
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                ImportAction::make('user_import')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->importer(UserImporter::class),
                ImportAction::make('teacher_import')
                    ->label('Import teachers')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->importer(TeacherProfileImporter::class),
            ]);
    }
}
