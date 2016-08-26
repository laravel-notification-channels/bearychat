<?php

namespace NotificationChannels\BearyChat;

use NotificationChannels\BearyChat\Exceptions\CouldNotSendNotification;
use NotificationChannels\BearyChat\Events\MessageWasSent;
use NotificationChannels\BearyChat\Events\SendingMessage;
use Illuminate\Notifications\Notification;

class BearyChatChannel
{
    public function __construct()
    {
        // Initialisation code here
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @throws \NotificationChannels\BearyChat\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        //$response = [a call to the api of your notification send]

//        if ($response->error) { // replace this by the code need to check for errors
//            throw CouldNotSendNotification::serviceRespondedWithAnError($response);
//        }
    }
}
