<?php declare(strict_types=1);

namespace TwitchBot\Output;

use WebSocket\Client;

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
        $client = new Client($this->url);
        $client->send($json);
        $client->close();
    }
}
