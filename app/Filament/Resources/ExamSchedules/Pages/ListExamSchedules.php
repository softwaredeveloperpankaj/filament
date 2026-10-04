<?php

namespace App\Filament\Resources\ExamSchedules\Pages;

use App\Filament\Resources\ExamSchedules\ExamScheduleResource;
use Filament\Resources\Pages\ListRecords;

class ListExamSchedules extends ListRecords
{
    protected static string $resource = ExamScheduleResource::class;
}