<?php declare(strict_types=1);

namespace TwitchBot\Websocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Broadcaster implements MessageComponentInterface {
    /** @var \SplObjectStorage */
    protected $clients;

    /**
     * @return void
     */
    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    /**
     * @param ConnectionInterface $conn
     *
     * @return void
     */
    public function onOpen(ConnectionInterface $conn): void {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
    }

    /**
     * @param ConnectionInterface $from
     *
     * @return void
     */
    public function onMessage(ConnectionInterface $from, $msg): void {
        /** @var ConnectionInterface $client */
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn): void {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
    }

    /**
     *
     * @param ConnectionInterface $conn
     * @param \Exception $e
     *
     * @return void
     */
    public function onError(ConnectionInterface $conn, \Exception $e): void {
        trigger_error("An error has occurred: {$e->getMessage()}\n", E_USER_WARNING);
        $conn->close();
    }
}
