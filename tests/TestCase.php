<?php

namespace NotificationChannels\BearyChat\Test;

use Orchestra\Testbench\TestCase as OrchestraCase;
use Mockery;
use GuzzleHttp\Client as HttpClient;
use ElfSundae\BearyChat\Laravel\ClientManager;

class TestCase extends OrchestraCase
{
    /**
     * @var \ElfSundae\BearyChat\Laravel\ClientManager
     */
    protected $clientManager;

    public function setUp()
    {
        parent::setUp();

        $this->setupClientManager();
    }

    public function tearDown()
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function setupClientManager()
    {
        $clientManager = new ClientManager($this->app);
        $clientManager->customHttpClient(function ($name) {
            $response = class_exists('GuzzleHttp\Message\Response') ?
                (new \GuzzleHttp\Message\Response(200)) :
                (new \GuzzleHttp\Psr7\Response(200));
            $httpClient = Mockery::mock(HttpClient::class);
            $httpClient->shouldReceive('post')
                ->andReturn($response);

            return $httpClient;
        });

        $this->clientManager = Mockery::mock($clientManager);
    }
}
