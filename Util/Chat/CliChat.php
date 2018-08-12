<?php

namespace TwitchBot\Util\Chat;

use TwitchBot\Util\EventManager;
use TwitchBot\Util\Message;
use TwitchBot\Util\Chat\ChatInterface;

class CliChat implements ChatInterface
{
    /**
     * @param string $data
     *
     * @return void
     */
    public function send($data)
    {
        echo $data . "\n";
    }

    /**
     * @param string $channel
     * @param string $message
     *
     * @return void
     */
    public function sendMessage($channel, $message)
    {
        echo $message . "\n";
    }
}
