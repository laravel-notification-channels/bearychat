<?php

namespace NotificationChannels\BearyChat;

use NotificationChannels\BearyChat\Exceptions\CouldNotSendNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use ElfSundae\BearyChat\Laravel\ClientManager;
use ElfSundae\BearyChat\Client;
use ElfSundae\BearyChat\Message;

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
     *
     * @throws \NotificationChannels\BearyChat\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toBearyChat($notifiable);

        if (! (is_object($message) && $message instanceof Message)) {
            throw CouldNotSendNotification::invalidMessage($message);
        }

        $client = $message->getClient();

        if ($route = $notifiable->routeNotificationFor('BearyChat')) {
            if (Str::startsWith($route, ['@', '#'])) {
                $message->to($route);
            } else if (Str::startsWith($route, ['http://', 'https://'])) {
                $client = $client ? $client->webhook($route) : new Client($route);
            } else {
                $clientName = $route;
            }
        }

        if (is_null($client) || isset($clientName)) {
            $client = $this->clientManager->client(isset($clientName) ? $clientName : null);
            $message = $this->applyMessageDefaultsFromClient($client);
        }

        if (! $client->sendMessage($message)) {
            throw CouldNotSendNotification::sendingFailed($client->getWebhook(), $message);
        }
    }

    /**
     * Apply message defaults from a client to a message instance.
     *
     * @param  \ElfSundae\BearyChat\Client  $client
     * @param  \ElfSundae\BearyChat\Message  $message
     * @return \ElfSundae\BearyChat\Message
     */
    protected function applyMessageDefaultsFromClient($client, $message)
    {
        return $message;
    }
}
