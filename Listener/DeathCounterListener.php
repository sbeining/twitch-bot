<?php

namespace TwitchBot\Listener;

require_once __DIR__.'/../Util/Message.php';
require_once __DIR__.'/BaseMessageListener.php';

use TwitchBot\Util\Message;

class DeathCounterListener extends BaseMessageListener
{
    /** @var int */
    private $count = 0;

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
        if (preg_match('/!you_died/', $text, $matches)) {
            $this->count++;
        } else if (preg_match('/!undo_death/', $text, $matches)) {
            $this->count--;
        } else if (preg_match('/!set_deaths (.*)/', $text, $matches)) {
            $this->count = intval($matches[1]);
        } else if (preg_match('/!deaths/', $text, $matches)) {
            $this->chat->sendMessage($channel, "{$channel} died {$this->count} times.");
        }
    }
}
