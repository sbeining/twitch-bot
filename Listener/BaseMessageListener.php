<?php

namespace TwitchBot\Listener;

use TwitchBot\Util\Chat;
use TwitchBot\Util\Message;

abstract class BaseMessageListener implements ListenerInterface
{
    /** @var Chat */
    protected $chat;

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
        return ['PRIVMSG'];
    }

    /**
     * @param array $payload
     *
     * @return void
     */
    public function execute($payload)
    {
        $message = $payload[0];
        list($user,) = explode('!', $message->getPrefix());
        $channel = $message->getParams()[0];
        $text = $message->getParams()[1];

        $this->handleMessage($user, $channel, $text, $message);
    }

    /**
     * @param string $user
     * @param string $channel
     * @param string $text
     * @param Message $message
     *
     * @return void
     */
    abstract protected function handleMessage($user, $channel, $text, Message $message);

    /**
     * @param array $words
     *
     * @return string
     */
    protected function enumerate(array $words): string {
        $result = '';
        $cnt = 0;
        $max = count($words);

        foreach ($words as $name) {
            $result .= "{$name}";

            $cnt++;

            if ($cnt === $max) {
                continue;
            } else if ($cnt === $max - 1) {
                $result .= ' and ';
            } else {
                $result .= ', ';
            }
        }

        return $result;
    }

}
