<?php

namespace App\Filament\Resources\Exams\RelationManagers;

use App\Enums\ResultStatus;
use App\Enums\GradeScale;
use App\Models\ExamResult;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\Auth;

class ExamResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'results';
    protected static ?string $title = 'Exam Results';
    protected static ?string $recordTitleAttribute = 'student.name';
    protected static ?string $permissionPrefix = 'exam_result';

    public function schema(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Result Details')
                ->schema([
                    Grid::make(2)->schema([

                        // Total Obtained (read-only)
                        TextInput::make('grand_total_obtained')
                            ->label('Total Obtained')
                            ->numeric()
                            ->disabled(),

                        // Total Maximum (read-only)
                        TextInput::make('grand_total_maximum')
                            ->label('Total Maximum')
                            ->numeric()
                            ->disabled(),

                        // Percentage (read-only)
                        TextInput::make('overall_percentage')
                            ->label('Percentage')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%')
                            ->disabled(),

                        // Overall Grade (read-only, set by service)
                        Select::make('overall_grade')
                            ->label('Overall Grade')
                            ->options(GradeScale::forFilamentSelect())
                            ->disabled(),

                        // Status (editable by principal/admin)
                        Select::make('status')
                            ->label('Status')
                            ->options(ResultStatus::forFilamentSelect())
                            ->required(),

                        // Rank in Section (read-only)
                        TextInput::make('rank_in_section')
                            ->label('Rank in Section')
                            ->numeric()
                            ->disabled(),

                        // Rank in Class (read-only)
                        TextInput::make('rank_in_class')
                            ->label('Rank in Class')
                            ->numeric()
                            ->disabled(),

                        // Rank in Branch (read-only)
                        TextInput::make('rank_in_branch')
                            ->label('Rank in Branch')
                            ->numeric()
                            ->disabled(),

                        // Failed Subjects (read-only JSON)
                        Textarea::make('failed_subjects')
                            ->label('Failed Subjects')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('student.name')
            ->columns([
                TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('student.roll_no')
                    ->label('Roll No')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('grand_total_obtained')
                    ->label('Obtained')
                    ->sortable()
                    ->suffix(fn($record) => ' / ' . $record->grand_total_maximum),

                TextColumn::make('overall_percentage')
                    ->label('Percentage')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn($state) => $state >= 75 ? 'success' : ($state >= 50 ? 'warning' : 'danger'))
                    ->weight('bold'),

                TextColumn::make('overall_grade')
                    ->label('Grade')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('rank_in_section')
                    ->label('Section Rank')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('rank_in_class')
                    ->label('Class Rank')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('failed_subjects_count')
                    ->label('Failed')
                    ->getStateUsing(fn($record) => $record->failed_subjects ? count($record->failed_subjects) : 0)
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success'),
            ])
            ->defaultSort('rank_in_section')
            ->headerActions([])
            ->recordActions([
                ActionGroup::make([
                    // View detailed result (opens result page)
                    Action::make('view_result')
                        ->label('View Result')
                        ->icon('heroicon-o-eye')
                        ->url(fn(ExamResult $record) => route('exams.result', ['exam' => $this->getOwnerRecord(), 'student' => $record->student]))
                        ->openUrlInNewTab(),

                    // Publish individual result
                    Action::make('publish')
                        ->label('Publish')
                        ->icon('heroicon-o-megaphone')
                        ->color('success')
                        ->visible(fn(ExamResult $record) => $record->status === ResultStatus::DRAFT)
                        ->requiresConfirmation()
                        ->action(fn(ExamResult $record) => $record->publish(Auth::user())),

                    // Withhold published result
                    Action::make('withhold')
                        ->label('Withhold')
                        ->icon('heroicon-o-pause-circle')
                        ->color('warning')
                        ->visible(fn(ExamResult $record) => $record->status === ResultStatus::PUBLISHED)
                        ->requiresConfirmation()
                        ->action(fn(ExamResult $record) => $record->update(['status' => ResultStatus::WITHHELD])),

                    EditAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Bulk publish selected results
                    Action::make('bulk_publish')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-megaphone')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records
                            ->where('status', ResultStatus::DRAFT)
                            ->each(fn($r) => $r->publish(Auth::user()))),

                    // Bulk withhold
                    Action::make('bulk_withhold')
                        ->label('Withhold Selected')
                        ->icon('heroicon-o-pause-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records
                            ->where('status', ResultStatus::PUBLISHED)
                            ->each(fn($r) => $r->update(['status' => ResultStatus::WITHHELD]))),

                    DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()?->hasRole('super_admin') ?? false),
                ]),
            ]);
    }
}