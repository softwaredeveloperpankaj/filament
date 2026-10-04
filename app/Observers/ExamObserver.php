<?php

namespace App\Observers;

use App\Models\Exam;
use Illuminate\Support\Str;

class ExamObserver
{
    /**
     * Handle the Exam "creating" event.
     */
    public function creating(Exam $exam): void
    {
        if (empty($exam->slug)) {
            $baseSlug = Str::slug($exam->name);
            $slug = $baseSlug;
            $count = 1;

            while (Exam::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$count}";
                $count++;
            }

            $exam->slug = $slug;
        }
    }
}