<?php

namespace NotificationChannels\BearyChat\Test;

use Mockery;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response as HttpResponse;
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

    public function testNotificationCanBeSent()
    {
        $client = Mockery::mock(Client::class)->makePartial();
        $client->shouldReceive('sendMessage')
            ->once()
            ->with(Mockery::on(function ($argument) {
                return $argument instanceof Message;
            }))
            ->andReturn(true);

        $this->clientManager->shouldReceive('client')->andReturn($client);

        $channel = $this->getChannel();
        $channel->send(new TestNotifiable(), new TestNotification);
    }

    public function testCouldNotSendNotificationExceptionForInvalidMessage()
    {
        $this->setExpectedException(CouldNotSendNotification::class);

        $channel = $this->getChannel();
        $channel->send(new TestNotifiable(), new TestInvalidMessageNotification);
    }

    public function testCouldNotSendNotificationExceptionForSendingFailed()
    {
        $this->setExpectedException(CouldNotSendNotification::class);

        $client = Mockery::mock(Client::class)->makePartial();
        $client->shouldReceive('sendMessage')
            ->andReturn(false);

        $this->clientManager->shouldReceive('client')->andReturn($client);

        $channel = $this->getChannel();
        $channel->send(new TestNotifiable(), new TestNotification);
    }

    protected function getChannel()
    {
        return new BearyChatChannel($this->clientManager);
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
        return (new Message)->text('text');
    }
}

class TestInvalidMessageNotification extends Notification
{
    public function toBearyChat($notifiable)
    {
        return 'foo';
    }
}
