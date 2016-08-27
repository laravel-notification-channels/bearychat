<?php

namespace NotificationChannels\BearyChat\Test;

use Mockery;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Notifiable;
use ElfSundae\BearyChat\Client;
use ElfSundae\BearyChat\Message;
use ElfSundae\BearyChat\MessageDefaults;
use ElfSundae\BearyChat\Laravel\ClientManager;
use NotificationChannels\BearyChat\BearyChatChannel;
use NotificationChannels\BearyChat\Exceptions\CouldNotSendNotification;

class BearyChatChannelTest extends TestCase
{
    public function testInstantiation()
    {
        $this->assertInstanceOf(BearyChatChannel::class, $this->getChannel());
    }

    protected function getChannel()
    {
        return new BearyChatChannel($this->clientManager);
    }

    protected function getNotifiable()
    {
        return new TestNotifiable;
    }
}

class TestNotifiable
{
    use Notifiable;

    public function routeNotificationForBearyChat()
    {
        return 'user';
    }
}

class TestNotification extends Notification
{
    public function toBearyChat($notifiable)
    {
        return (new Message)
            ->text('text')
            ->add('attachment content', 'attachment title');
    }
}
