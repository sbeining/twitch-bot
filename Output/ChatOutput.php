<?php declare(strict_types=1);

namespace TwitchBot\Output;

use TwitchBot\Chat\Chat;

class ChatOutput implements OutputInterface {
    /** @var Chat */
    private $chat;

    /** @var string */
    private $channel;

    /**
     * @param Chat $chat
     * @param string $channel
     *
     * @return void
     */
    public function __construct(Chat $chat, string $channel) {
        $this->chat = $chat;
        $this->channel = $channel;
    }

    /**
     * @param string $json
     *
     * @return void
     */
    public function tell(string $json): void {
        $data = json_decode($json, true);

        if (isset($data['command'])) {
            $this->chat->send($data['raw']);
        } else {
            $this->chat->sendMessage($this->channel, $data['content']);
        }
    }
}
