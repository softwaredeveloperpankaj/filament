<?php

namespace App\Filament\Resources\BranchClasses\RelationManagers;

use App\Models\Section;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClassSectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'classSections';

    protected static ?string $title = 'Assigned Sections';

    protected static ?string $modelLabel = 'Class Section';

    protected static ?string $pluralModelLabel = 'Assigned Sections';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('branch_id')
                ->default(fn () => $this->getOwnerRecord()->branch_id),

            Select::make('section_id')
                ->label('Section')
                ->required()
                ->searchable()
                ->preload()
                ->options(function (): array {
                    $ownerRecord = $this->getOwnerRecord();

                    $usedSectionIds = $ownerRecord->classSections()
                        ->pluck('section_id')
                        ->all();

                    return Section::query()
                        ->where('branch_id', $ownerRecord->branch_id)
                        ->whereNotIn('id', $usedSectionIds)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all();
                }),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('section.name')
            ->columns([
                TextColumn::make('section.name')
                    ->label('Section')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('section.branch.name')
                    ->label('Branch')
                    ->badge()
                    ->sortable(),

                TextColumn::make('section.starting_roll_no')
                    ->label('Section Start Roll No')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Assigned At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['section.branch']))
            ->headerActions([
                CreateAction::make()
                    ->label('Assign Section')
                    ->mutateDataUsing(function (array $data): array {
                        $data['branch_class_id'] = $this->getOwnerRecord()->getKey();
                        $data['branch_id'] = $this->getOwnerRecord()->branch_id;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['branch_id'] = $this->getOwnerRecord()->branch_id;

                        return $data;
                    }),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}