<?php

namespace NotificationChannels\BearyChat;

use Illuminate\Support\Str;
use ElfSundae\BearyChat\Client;
use ElfSundae\BearyChat\Message;
use Illuminate\Notifications\Notification;
use ElfSundae\BearyChat\Laravel\ClientManager;
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

        $client = $message->getClient();

        if ($route = $notifiable->routeNotificationFor('BearyChat')) {

            // Route can be an user, a channel, a webhook endpoint,
            // or a client name which correspond to one of the clients
            // listed in the BearyChat configuration file.

            if (Str::startsWith($route, ['@', '#'])) {
                $message->to($route);
            } elseif (Str::startsWith($route, ['http://', 'https://'])) {
                $client = $client ? $client->webhook($route) : new Client($route);
            } else {
                $clientName = $route;
            }
        }

        // If the message is not created from a client, or the notifiable object
        // provides a different client to send this message, we should obtain
        // the client via the ClientManager and apply any message defaults from
        // this client to the message instance.

        if (is_null($client) || isset($clientName)) {
            $client = $this->clientManager->client(isset($clientName) ? $clientName : null);

            $message->configureDefaults($client->getMessageDefaults(), true);
        }

        if (! $client->sendMessage($message)) {
            throw CouldNotSendNotification::sendingFailed($client->getWebhook(), $message);
        }
    }
}
