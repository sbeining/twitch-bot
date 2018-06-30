<?php

namespace TwitchBot\Listener;

require_once __DIR__.'/../Util/Message.php';
require_once __DIR__.'/BaseMessageListener.php';

use TwitchBot\Util\Message;

class ConversationListener extends BaseMessageListener
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
        if ($text == "Hi @{$this->chat->getNick()}") {
            $this->chat->sendMessage($channel, "Hi @{$user} bleedPurple");
        }
    }
}
