<?php

namespace Aztech\Events\Bus\Plugins\Redis;

use Aztech\Events\Bus\Channel\ChannelReader;
use Predis\Client;

class RedisChannelReader implements ChannelReader
{

    private $client;

    private $key = null;

    private $processingKey = null;

    public function __construct(Client $redisClient, $eventKey, $processingKey = null)
    {
        if (empty($eventKey)) {
            throw new \InvalidArgumentException('Event key must be provided.');
        }

        $this->client = $redisClient;
        $this->key = $eventKey;
        $this->processingKey = $processingKey;
    }

    public function read()
    {
        if (! empty($this->processingKey)) {
            return $this->client->rpoplpush($this->key, $this->processingKey, 0);
        }
        else {
            return $this->client->rpop(array(
                $this->key
            ), 0);
        }
    }

    public function dispose()
    {
        while ($this->client->rpoplpush($this->processingKey, $this->key)) {
            continue;
        }
    }
}
