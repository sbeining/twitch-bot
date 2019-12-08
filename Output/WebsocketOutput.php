<?php declare(strict_types=1);

namespace TwitchBot\Output;

use WebSocket\Client;
use WebSocket\ConnectionException;

class WebsocketOutput implements OutputInterface
{
    /** @var string */
    private $url;

    /**
     * @param string $url
     *
     * @return void
     */
    public function __construct(string $url) {
        $this->url = $url;
    }

    /**
     * @param string $json
     *
     * @return void
     */
    public function tell(string $json): void {
        try {
            $client = new Client($this->url);
            $client->send($json);
            $client->close();
        } catch (ConnectionException $e) {
        }
    }
}
