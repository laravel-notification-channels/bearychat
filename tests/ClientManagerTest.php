<?php

namespace NotificationChannels\BearyChat\Test;

use ElfSundae\BearyChat\Client;
use ElfSundae\BearyChat\Laravel\ClientManager;

class ClientManagerTest extends TestCase
{
    public function testInstantiation()
    {
        $this->assertInstanceOf(ClientManager::class, $this->clientManager);

        $this->assertInstanceOf(Client::class, $this->clientManager->client());
    }
}
