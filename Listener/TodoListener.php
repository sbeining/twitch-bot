<?php

namespace TwitchBot\Listener;

use TwitchBot\Util\Chat\ChatInterface;
use TwitchBot\Util\Message;
use TwitchBot\Util\Persistence;

class TodoListener extends BaseMessageListener
{
    /** @var array */
    private $todoList = [];

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

        if (!isset($data[$channel]['todo'])) {
            return [];
        }

        $todo = $data[$channel]['todo'];

        return $todo;
    }

    /**
     * @param string $channel
     * @param array $todo
     *
     * @return void
     */
    private function save($channel, $todo)
    {
        $data = $this->persistence->getData();

        if (!isset($data[$channel])) {
            $data[$channel] = [];
        }

        if (!isset($data[$channel]['todo'])) {
            $data[$channel]['todo'] = [];
        }

        $data[$channel]['todo'] = array_values($todo);

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
        $this->todo = $this->load($channel);

        if (preg_match('/^!done (.*)/', $text, $matches)) {
            $index = $matches[1];
            if (isset($this->todo[$index])) {
                $task = $this->todo[$index];
                unset($this->todo[$index]);
                $this->chat->sendMessage($channel, "{$task} - done!");
            } else {
                $this->chat->sendMessage($channel, "{$index} not found!");
            }
        } else if (preg_match('/^!add_task (.*)/', $text, $matches)) {
            $task = $matches[1];
            $this->todo[] = $task;
            $this->chat->sendMessage($channel, "{$task} added");
        } else if (preg_match('/^!todos/', $text)) {
            $todos = [];
            if (empty($this->todo)) {
                $this->chat->sendMessage($channel, "Everything done!");
            } else {
                foreach ($this->todo as $index => $todo) {
                  $todos[] = "({$index}). {$todo}";
                }
                $todos = $this->enumerate($todos);
                $this->chat->sendMessage($channel, "{$todos}");
            }
        }

        $this->save($channel, $this->todo);
    }
}
