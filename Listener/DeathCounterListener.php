<?php

namespace TwitchBot\Listener;

use TwitchBot\Util\Chat\ChatInterface;
use TwitchBot\Util\Message;
use TwitchBot\Util\Persistence;

class DeathCounterListener extends BaseMessageListener
{
    /** @var int */
    private $count = 0;

    /**
     * @param ChatInterface $chat
     * @param Persistence $persistence
     *
     * @return void
     */
    public function __construct(ChatInterface $chat, Persistence $persistence)
    {
        $this->chat = $chat;
        $this->persistence = $persistence;
    }

    /**
     * @param string $channel
     *
     * @return int
     */
    private function load($channel)
    {
        $data = $this->persistence->getData();

        if (!isset($data[$channel])) {
            return 0;
        }

        if (!isset($data[$channel]['death_count'])) {
            return 0;
        }

        return $data[$channel]['death_count']['count'];
    }

    /**
     * @param string $channel
     * @param int $count
     *
     * @return void
     */
    private function save($channel, $count)
    {
        $data = $this->persistence->getData();

        if (!isset($data[$channel])) {
            $data[$channel] = [];
        }

        if (!isset($data[$channel]['death_count'])) {
            $data[$channel]['death_count'] = [];
        }

        $data[$channel]['death_count']['count'] = $count;

        $this->persistence->setData($data);
        $this->persistence->save();
    }

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
        $this->count = $this->load($channel);

        if (preg_match('/!you_died/', $text, $matches)) {
            $this->count++;
        } else if (preg_match('/!undo_death/', $text, $matches)) {
            $this->count--;
        } else if (preg_match('/!set_deaths (.*)/', $text, $matches)) {
            $this->count = intval($matches[1]);
        } else if (preg_match('/!deaths/', $text, $matches)) {
            $this->chat->sendMessage($channel, "{$channel} died {$this->count} times.");
        }

        $this->save($channel, $this->count);
    }
}
