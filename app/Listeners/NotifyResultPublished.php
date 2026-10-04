<?php

namespace App\Listeners;

use App\Events\ResultPublished;
use App\Notifications\ResultPublished as ResultPublishedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyResultPublished implements ShouldQueue
{
    public function handle(ResultPublished $event): void
    {
        // Notify branch users and enrolled students
    }
}