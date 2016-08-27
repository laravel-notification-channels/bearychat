<?php

namespace NotificationChannels\BearyChat\Exceptions;

use ElfSundae\BearyChat\Message;

class CouldNotSendNotification extends \Exception
{
    /**
     * Thrown when an invalid message was passed.
     *
     * @param  mixed $message
     * @return static
     */
    public static function invalidMessage($message)
    {
        $type = is_object($message) ? get_class($message) : gettype($message);

        return new static('The message should be an instance of '.Message::class.". Given `{$type}` is invalid.");
    }

    /**
     * Thrown when sending failed.
     *
     * @param  string  $webhook
     * @param  mixed  $payload
     * @return static
     */
    public static function sendingFailed($webhook, $payload)
    {
        if (! is_string($payload)) {
            $payload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }

        return new static("Failed sending to BearyChat with webhook {$webhook} .\n{$payload}");
    }
}
