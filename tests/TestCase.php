<?php

namespace NotificationChannels\BearyChat\Test;

use Orchestra\Testbench\TestCase as OrchestraCase;
use Mockery;
use ElfSundae\BearyChat\Client;
use ElfSundae\BearyChat\Laravel\ClientManager;
use NotificationChannels\BearyChat\BearyChatChannel;

class TestCase extends OrchestraCase
{
    /**
     * @var \ElfSundae\BearyChat\Laravel\ClientManager
     */
    protected $clientManager;

    public function setUp()
    {
        parent::setUp();

        $this->clientManager = new ClientManager($this->app);

        $this->assertInstanceOf(ClientManager::class, $this->clientManager);

        $this->assertInstanceOf(Client::class, $this->clientManager->client());
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('bearychat.default', 'testbench');
        $app['config']->set('bearychat.clients.testbench', [
            'webhook' => 'http://fake.endpoint',
        ]);
    }
}
