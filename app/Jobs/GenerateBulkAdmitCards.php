<?php

namespace App\Jobs;

use App\Models\ExamStudentEntry;
use App\Services\AdmitCardService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class GenerateBulkAdmitCards implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $entryIds) {}

    public function handle(): void
    {
        $entries = ExamStudentEntry::whereIn('id', $this->entryIds)->get();
        AdmitCardService::markPrinted($entries);
    }
}