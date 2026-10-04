<?php

namespace App\Providers;

use App\Events\AdmitCardGenerated;
use App\Events\ExamScheduled;
use App\Events\ResultCalculated;
use App\Events\ResultPublished;
use App\Listeners\NotifyResultPublished;
use App\Listeners\SendAdmitCardNotification;
use App\Listeners\SendExamReminder;
use App\Listeners\UpdateStudentRank;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\ExamResult;
use App\Models\OnlineExamAttempt;
use App\Observers\ExamMarkObserver;
use App\Observers\ExamObserver;
use App\Observers\ExamResultObserver;
use App\Observers\OnlineExamAttemptObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Model Observers
        Exam::observe(ExamObserver::class);
        ExamMark::observe(ExamMarkObserver::class);
        ExamResult::observe(ExamResultObserver::class);
        OnlineExamAttempt::observe(OnlineExamAttemptObserver::class);

        // Event Listeners
        Event::listen(AdmitCardGenerated::class, SendAdmitCardNotification::class);
        Event::listen(ExamScheduled::class, SendExamReminder::class);
        Event::listen(ResultPublished::class, NotifyResultPublished::class);
        Event::listen(ResultCalculated::class, UpdateStudentRank::class);
    }
}