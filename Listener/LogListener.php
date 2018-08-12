<?php

namespace TwitchBot\Listener;

use TwitchBot\Util\Message;

class LogListener extends BaseMessageListener
{
    /**
     * @param string $user
     * @param string $channel
     * @param string $text
     * @param Message $message
     *
     * @return void
     */
    public function handleMessage($user, $channel, $text, Message $message)
    {
        echo "{$channel} {$user}: {$text}\n";
    }
}
