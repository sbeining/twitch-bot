<?php

namespace TwitchBot\Util\Chat;

interface ChatInterface
{
    /**
     * @param mixed $payload
     *
     * @return void
     */
    public function send($payload);

    /**
     * @param string channel
     * @param string $message
     *
     * @return void
     */
    public function sendMessage($channel, $message);
}
