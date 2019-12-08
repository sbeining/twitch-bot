<?php declare(strict_types=1);

namespace TwitchBot\Output;

use TwitchBot\Chat\Chat;
use TwitchBot\Output\OutputTraits\PokemonTextOutput;

class ChatOutput implements OutputInterface {
    use PokemonTextOutput;

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

        if (isset($data['pokemon'])) {
            $this->say($this->transformPokemon($data));
        } else if (isset($data['command'])) {
            $this->chat->send($data['raw']);
        } else if (isset($data['content'])) {
            $lines = explode("\n", $data['content']);
            $this->multiline($lines);
        }
    }

    /**
     * @param array $lines
     *
     * @return void
     */
    private function multiline(array $lines): void
    {
        foreach ($lines as $line) {
            $this->say($line);
            $this->breathe();
        }
    }

    /**
     * @param string $line
     *
     * @return void
     */
    private function say(string $line): void
    {
        $this->chat->sendMessage($this->channel, $line);
    }

    /**
     * @return void
     */
    private function breathe(): void
    {
        usleep(500000);
    }
}
