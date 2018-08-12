<?php

namespace TwitchBot\Listener;

use TwitchBot\Util\Chat\ChatInterface;
use TwitchBot\Util\Message;
use TwitchBot\Util\Persistence;

class BossesKilledListener extends BaseMessageListener
{
    /** @var array */
    private $vanquished = [];

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
     * @return array
     */
    private function load($channel)
    {
        $data = $this->persistence->getData();

        if (!isset($data[$channel])) {
            return [];
        }

        if (!isset($data[$channel]['bosses_killed'])) {
            return [];
        }

        $vanquished = $data[$channel]['bosses_killed']['vanquished'];

        return array_combine($vanquished, $vanquished);
    }

    /**
     * @param string $channel
     * @param array $vanquished
     *
     * @return void
     */
    private function save($channel, $vanquished)
    {
        $data = $this->persistence->getData();

        if (!isset($data[$channel])) {
            $data[$channel] = [];
        }

        if (!isset($data[$channel]['bosses_killed'])) {
            $data[$channel]['bosses_killed'] = [];
        }

        $data[$channel]['bosses_killed']['vanquished'] = array_values($vanquished);

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
        $this->vanquished = $this->load($channel);

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

        $this->save($channel, $this->vanquished);
    }
}
