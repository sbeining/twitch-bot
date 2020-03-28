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
     * @param string $inJson Input for reference
     *
     * @return void
     */
    public function tell(string $json, string $inJson): void {
        $out = json_decode($json, true);
        $in = json_decode($inJson, true);

        if ($in['channel']) {
            $out['channel'] = $in['channel'];
        }

        try {
            $client = new Client($this->url);
            $client->send(json_encode($out));
            $client->close();
        } catch (ConnectionException $e) {
        }
    }
}
