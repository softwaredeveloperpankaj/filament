<?php

namespace App\Listeners;

use App\Events\AdmitCardGenerated;
use App\Notifications\AdmitCardAvailable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAdmitCardNotification implements ShouldQueue
{
    public function handle(AdmitCardGenerated $event): void
    {
        $user = $event->entry->student?->branch?->user;
        if ($user) {
            $user->notify(new AdmitCardAvailable($event->entry));
        }
    }
}