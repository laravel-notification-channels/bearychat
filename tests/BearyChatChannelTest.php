<?php

namespace NotificationChannels\BearyChat\Test;

use Mockery;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Notifiable;
use ElfSundae\BearyChat\Client;
use ElfSundae\BearyChat\Message;
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
        $client = $this->prepareClient();

        $client->shouldReceive('sendMessage')
            ->once()
            ->with(Mockery::on(function ($argument) {
                return $argument instanceof Message;
            }))
            ->andReturn(true);

        $this->getChannel()
            ->send(new TestNotifiable(), new TestNotification());
    }

    public function testCouldNotSendNotificationExceptionForInvalidMessage()
    {
        $this->setExpectedException(CouldNotSendNotification::class);

        $this->getChannel()
            ->send(new TestNotifiable(), new TestNotification('foo'));
    }

    public function testCouldNotSendNotificationExceptionForSendingFailed()
    {
        $this->setExpectedException(CouldNotSendNotification::class);

        $client = $this->prepareClient();

        $client->shouldReceive('sendMessage')
            ->andReturn(false);

        $this->getChannel()
            ->send(new TestNotifiable(), new TestNotification());
    }

    public function testMessageWhenNotifiableGivesUser()
    {
        $client = $this->prepareClient();

        $client->shouldReceive('sendMessage')
            ->once()
            ->with(Mockery::on(function ($message) {
                return $message->getUser() === 'foo';
            }))
            ->andReturn(true);

        $message = (new Message)->user('user');

        $this->getChannel()
            ->send(new TestNotifiable('@foo'), new TestNotification($message));
    }

    public function testMessageWhenNotifiableGivesChannel()
    {
        $client = $this->prepareClient();

        $client->shouldReceive('sendMessage')
            ->once()
            ->with(Mockery::on(function ($message) {
                return $message->getChannel() === 'foo';
            }))
            ->andReturn(true);

        $message = (new Message)->channel('user');

        $this->getChannel()
            ->send(new TestNotifiable('#foo'), new TestNotification($message));
    }

    protected function getChannel()
    {
        return new BearyChatChannel($this->clientManager);
    }

    protected function getClient()
    {
        return Mockery::mock(Client::class)->makePartial();
    }

    protected function returnClientForClientManager($client)
    {
        $this->clientManager->shouldReceive('client')->andReturn($client);
    }

    protected function prepareClient()
    {
        $client = $this->getClient();
        $this->returnClientForClientManager($client);

        return $client;
    }
}

class TestNotifiable
{
    use Notifiable;

    protected $route;

    public function __construct($route = null)
    {
        $this->route = $route;
    }

    public function routeNotificationForBearyChat()
    {
        return $this->route;
    }
}

class TestNotification extends Notification
{
    protected $message;

    public function __construct($message = null)
    {
        $this->message = is_null($message) ? (new Message)->text('text') : $message;
    }

    public function toBearyChat($notifiable)
    {
        return $this->message;
    }
}
