<?php

namespace TwitchBot\Listener;

require_once __DIR__.'/../Util/Message.php';
require_once __DIR__.'/BaseMessageListener.php';

use TwitchBot\Util\Message;

class BossesKilledListener extends BaseMessageListener
{
    /** @var array */
    private $vanquished = [];

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
        if (preg_match('/!unvanquish (.*)/', $text, $matches)) {
            $name = $matches[1];
            if (isset($this->vanquished[$name])) {
                unset($this->vanquished[$name]);
                $this->chat->sendMessage($channel, "{$name} was revived.");
            } else {
                $this->chat->sendMessage($channel, "{$name} is still alive.");
            }
        } else if (preg_match('/!vanquish (.*)/', $text, $matches)) {
            $name = $matches[1];
            $this->vanquished[$name] = $name;
            $this->chat->sendMessage($channel, "{$name} was killed.");
        } else if (preg_match('/!vanquished/', $text, $matches)) {
            $vanquishedText = $this->enumerate($this->vanquished);
            $this->chat->sendMessage($channel, "Bosses killed: {$vanquishedText}");
        }
    }
}
