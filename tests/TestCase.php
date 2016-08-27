<?php

namespace NotificationChannels\BearyChat\Test;

use Orchestra\Testbench\TestCase as OrchestraCase;
use Mockery;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response as HttpResponse;
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
        $this->clientManager = Mockery::instanceMock(new ClientManager($this->app));
        $this->clientManager->customHttpClient(function ($name) {
            $httpClient = Mockery::mock(HttpClient::class);
            $httpClient->shouldReceive('post')
                ->andReturn(new HttpResponse(200));
            return $httpClient;
        });
    }
}
