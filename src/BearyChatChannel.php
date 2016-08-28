<?php

namespace NotificationChannels\BearyChat;

use ElfSundae\BearyChat\Client;
use ElfSundae\BearyChat\Laravel\ClientManager;
use ElfSundae\BearyChat\Message;
use ElfSundae\BearyChat\MessageDefaults;
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
     *
     * @throws \NotificationChannels\BearyChat\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $this->getMessage($notifiable, $notification);

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

            $message = $this->applyMessageDefaultsFromClient($client, $message);
        }

        if (! $client->sendMessage($message)) {
            throw CouldNotSendNotification::sendingFailed($client->getWebhook(), $message);
        }
    }

    /**
     * Get the message from the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return \ElfSundae\BearyChat\Message
     *
     * @throws \NotificationChannels\BearyChat\Exceptions\CouldNotSendNotification if the
     *             passed message is not an instance of \ElfSundae\BearyChat\Message
     */
    protected function getMessage($notifiable, Notification $notification)
    {
        $message = $notification->toBearyChat($notifiable);

        if (! (is_object($message) && $message instanceof Message)) {
            throw CouldNotSendNotification::invalidMessage($message);
        }

        return $message;
    }

    /**
     * Apply message defaults from a client to a message instance.
     *
     * @param  \ElfSundae\BearyChat\Client  $client
     * @param  \ElfSundae\BearyChat\Message  $message
     * @return \ElfSundae\BearyChat\Message
     */
    protected function applyMessageDefaultsFromClient(Client $client, Message $message)
    {
        static $globalDefaultsKeys = null;

        if (is_null($globalDefaultsKeys)) {
            $globalDefaultsKeys = [
                MessageDefaults::CHANNEL,
                MessageDefaults::USER,
                MessageDefaults::MARKDOWN,
                MessageDefaults::NOTIFICATION,
            ];
        }

        foreach ($globalDefaultsKeys as $key) {
            $method = Str::studly($key);

            if (method_exists($message, $getMethod = 'get'.$method)) {
                if (is_null($message->{$getMethod}())) {
                    $message->{$method}($client->getMessageDefaults($key));
                }
            }
        }

        if (($attachmentColor = $client->getMessageDefaults(MessageDefaults::ATTACHMENT_COLOR))) {
            $attachmentDefaults = [
                'color' => $attachmentColor,
            ];

            // First we apply attachment defaults from the client to this message instance,
            // then reset message's attachments via its `setAttachments` method which can
            // handle its attachment defaults.

            $message->setAttachmentDefaults($attachmentDefaults + $message->getAttachmentDefaults());
            $message->setAttachments($message->getAttachments());
        }

        return $message;
    }
}
