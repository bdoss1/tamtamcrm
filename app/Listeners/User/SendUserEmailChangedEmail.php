<?php

namespace App\Listeners\User;

use App\Events\User\UserEmailChanged;
use App\Mail\User\UserEmailChangedNotification;
use Illuminate\Support\Facades\Mail;

class SendUserEmailChangedEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * @param UserEmailChanged $event
     */
    public function handle(UserEmailChanged $event)
    {
        Mail::to($event->user->email)->send(
            new UserEmailChangedNotification($event->user)
        );
    }
}
