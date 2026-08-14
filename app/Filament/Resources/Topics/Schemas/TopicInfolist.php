<?php

namespace App\Filament\Resources\Topics\Schemas;

use App\Models\Topic;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TopicInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Topic Details')
                    ->description('Basic information about this topic or chapter')
                    ->icon('heroicon-o-book-open')
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 3,
                    ])
                    ->schema([
                        TextEntry::make('branch.name')
                            ->label('Branch')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('subject.name')
                            ->label('Subject')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('name')
                            ->label('Topic / Chapter Name')
                            ->weight('medium'),

                        TextEntry::make('slug')
                            ->label('Slug')
                            ->copyable()
                            ->copyMessage('Slug copied'),

                        TextEntry::make('order')
                            ->label('Display Order')
                            ->numeric()
                            ->badge()
                            ->color('gray'),

                        IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                    ]),

                Section::make('Description')
                    ->description('Overview of what this topic covers')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('description')
                            ->label('')
                            ->placeholder('No description provided')
                            ->prose()
                            ->columnSpanFull(),
                    ]),

                Section::make('Record Information')
                    ->description('Topic record timestamps and deletion status')
                    ->icon('heroicon-o-information-circle')
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 3,
                    ])
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created On')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('—'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('—'),

                        TextEntry::make('deleted_at')
                            ->label('Deleted On')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('Not deleted')
                            ->badge()
                            ->color('danger')
                            ->visible(
                                fn (Topic $record): bool => $record->trashed()
                            ),
                    ]),
            ]);
    }
}