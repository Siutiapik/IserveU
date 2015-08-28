<?php

namespace App\Listeners\User;

use App\Events\UserLoginFailed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Hash;
use Mail;

class SendResetEmail
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
     * Handle the event.
     *
     * @param  UserLoginFailed  $event
     * @return void
     */
    public function handle(UserLoginFailed $event)
    {
        if(!$event->user){ //This email address wasn't associated with a user
            // Mail::send('emails.unknownemail',['event' => $event], function ($m) use ($event) {
            //     $m->to($event->credentials['email'], "Unknown Email Address")->subject('IserveU Login Attempts');
            // });
            return "no user with this email exists"; //Not sure how to handle event errors
        }

        $user = $event->user;

        if(true /*($user->login_attempts % 4) == 0 */){ //Every 4 attempts, send this email?
            
            if(empty($user->remember_token)){
                $hash = str_random(99);
                $user->remember_token = $hash;
                $user->save();
            }


            Mail::send('emails.passwordreset',['user' => $user], function ($m) use ($user) {
                $m->to($user->email, $user->first_name.' '.$user->last_name)->subject('Trouble Logging In?');
            });
            return 'password reset sent';
        }
    }
}
