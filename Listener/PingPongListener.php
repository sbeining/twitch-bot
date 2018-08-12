<?php

namespace TwitchBot\Listener;

use TwitchBot\Util\Chat;

class PingPongListener implements ListenerInterface
{
    /** @var Chat */
    private $chat;

    /**
     * @param Chat $chat
     *
     * @return void
     */
    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    /**
     * @return array
     */
    public function listensFor(): array
    {
        return ['PING'];
    }

    /**
     * @param array $payload
     *
     * @return void
     */
    public function execute($payload)
    {
        $message = $payload[0];
        $host = $message->getParams()[0];

        $this->chat->send("PONG :{$host}");
    }
}
