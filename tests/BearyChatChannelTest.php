<?php

namespace NotificationChannels\BearyChat\Test;

use ElfSundae\BearyChat\Client;
use ElfSundae\BearyChat\Laravel\ClientManager;
use ElfSundae\BearyChat\Message;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Mockery as m;
use NotificationChannels\BearyChat\BearyChatChannel;
use NotificationChannels\BearyChat\Exceptions\CouldNotSendNotification;
use PHPUnit\Framework\TestCase;

class BearyChatChannelTest extends TestCase
{
    public function testInstantiation()
    {
        $channel = new BearyChatChannel(m::mock(ClientManager::class));
        $this->assertInstanceOf(BearyChatChannel::class, $channel);
    }

    public function testSendInvalidMessage()
    {
        $this->expectException(CouldNotSendNotification::class);
        $this->expectExceptionCode(1);
        $channel = new BearyChatChannel(m::mock(ClientManager::class));
        $channel->send(new TestNotifiable, new TestNotification);
    }

    public function testSendMessageWithClient()
    {
        $client = m::mock(Client::class)
            ->shouldReceive('getMessageDefaults')
            ->andReturn([])
            ->shouldReceive('sendMessage')
            ->once()
            ->andReturn(true)
            ->mock();
        $channel = new BearyChatChannel(m::mock(ClientManager::class));
        $channel->send(new TestNotifiable, new TestNotification(new Message($client)));
    }

    public function testSendMessageFailed()
    {
        $client = m::mock(Client::class)
            ->shouldReceive('getMessageDefaults')
            ->andReturn([])
            ->shouldReceive('sendMessage')
            ->once()
            ->andReturn(false)
            ->shouldReceive('getWebhook')
            ->mock();
        $this->expectException(CouldNotSendNotification::class);
        $this->expectExceptionCode(4);
        $channel = new BearyChatChannel(m::mock(ClientManager::class));
        $channel->send(new TestNotifiable, new TestNotification(new Message($client)));
    }

    public function testSendMessageWithoutClient()
    {
        $message = new Message;
        $client = m::mock(Client::class)
            ->shouldReceive('getMessageDefaults')
            ->andReturn([])
            ->shouldReceive('sendMessage')
            ->with($message)
            ->once()
            ->andReturn(true)
            ->mock();
        $manager = m::mock(ClientManager::class)
            ->shouldReceive('client')
            ->andReturn($client)
            ->mock();
        $channel = new BearyChatChannel($manager);
        $channel->send(new TestNotifiable, new TestNotification($message));
    }

    public function testRouteToUser()
    {
        $client = m::mock(Client::class)
            ->shouldReceive('getMessageDefaults')
            ->andReturn([])
            ->shouldReceive('sendMessage')
            ->with(m::on(function ($message) {
                return $message->getUser() == 'elf';
            }))
            ->once()
            ->andReturn(true)
            ->mock();
        $message = new Message($client);
        $channel = new BearyChatChannel(m::mock(ClientManager::class));
        $channel->send(new TestNotifiable('@elf'), new TestNotification($message));
    }

    public function testRouteToChannel()
    {
        $client = m::mock(Client::class)
            ->shouldReceive('getMessageDefaults')
            ->andReturn([])
            ->shouldReceive('sendMessage')
            ->with(m::on(function ($message) {
                return $message->getChannel() == 'foobar';
            }))
            ->once()
            ->andReturn(true)
            ->mock();
        $message = new Message($client);
        $channel = new BearyChatChannel(m::mock(ClientManager::class));
        $channel->send(new TestNotifiable('#foobar'), new TestNotification($message));
    }

    public function testRouteToWebhook()
    {
        $client = m::mock(Client::class)
            ->shouldReceive('getMessageDefaults')
            ->andReturn([])
            ->shouldReceive('webhook')
            ->with('http://fake/webhook')
            ->andReturn(m::self())
            ->shouldReceive('sendMessage')
            ->once()
            ->andReturn(true)
            ->mock();
        $message = new Message($client);
        $channel = new BearyChatChannel(m::mock(ClientManager::class));
        $channel->send(new TestNotifiable('http://fake/webhook'), new TestNotification($message));
    }

    public function testRouteToClientName()
    {
        $client = m::mock(Client::class)
            ->shouldReceive('getMessageDefaults')
            ->andReturn([])
            ->shouldReceive('sendMessage')
            ->once()
            ->andReturn(true)
            ->mock();
        $manager = m::mock(ClientManager::class)
            ->shouldReceive('client')
            ->with('foobar')
            ->andReturn($client)
            ->mock();
        $message = new Message;
        $channel = new BearyChatChannel($manager);
        $channel->send(new TestNotifiable('foobar'), new TestNotification($message));
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
        $this->message = $message;
    }

    public function toBearyChat($notifiable)
    {
        return $this->message;
    }
}
