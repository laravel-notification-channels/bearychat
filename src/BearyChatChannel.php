<?php

namespace NotificationChannels\BearyChat;

use ElfSundae\BearyChat\Client;
use ElfSundae\BearyChat\Laravel\ClientManager;
use ElfSundae\BearyChat\Message;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use NotificationChannels\BearyChat\Exceptions\CouldNotSendNotification;

class BearyChatChannel
{
    /**
     * The BearyChat client manager.
     *
     * @var \ElfSundae\BearyChat\Laravel\ClientManager
     */
    protected $clientManager;

    /**
     * Create a new BearyChatChannel instance.
     *
     * @param  \ElfSundae\BearyChat\Laravel\ClientManager  $clientManager
     */
    public function __construct(ClientManager $clientManager)
    {
        $this->clientManager = $clientManager;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     *
     * @throws \NotificationChannels\BearyChat\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toBearyChat($notifiable);

        if (! $message instanceof Message) {
            throw CouldNotSendNotification::invalidMessage($message);
        }

        if ($route = $notifiable->routeNotificationFor('BearyChat')) {
            if (Str::startsWith($route, ['@', '#'])) {
                $message->to($route);
            } elseif (Str::startsWith($route, ['http://', 'https://'])) {
                if ($client = $message->getClient()) {
                    $client->webhook($route);
                } else {
                    $message->setClient(new Client($route));
                }
            } else {
                $message->setClient($this->clientManager->client($route));
            }
        }

        if (is_null($message->getClient())) {
            $message->setClient($this->clientManager->client());
        }

        if (! $message->send()) {
            throw CouldNotSendNotification::sendingFailed($message->getClient()->getWebhook(), $message);
        }
    }
}
