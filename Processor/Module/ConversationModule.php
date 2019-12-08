<?php declare(strict_types=1);

namespace TwitchBot\Processor\Module;

class ConversationModule extends Module {
    /** @var string */
    private $user;

    /** @var string */
    private $channel;

    /** @var string */
    private $text;

    /**
     * @param string $in
     *
     * @return string|null
     */
    public function handle(string $in): ?string {
        $data = json_decode($in, true);

        $this->user = $data['user'];
        $this->channel = $data['channel'];
        $this->text = $data['content'];

        if ($this->match("Hi ai_yekara")) {
            return json_encode([
                'content' => "Hi @{$this->user} bleedPurple",
            ]) ?: null;
        }

        if ($this->match("ai_yekara do the thing")) {
            return json_encode([
                'content' => implode("\n", array_merge(
                    [ "With pleasure @{$this->user} :)" ],
                    $this->getScheduler(),
                    $this->getFarewell()
                ))
            ]) ?: null;
        }

        return null;
    }

    private function getScheduler() {
        return [
            "Check out http://www.fronds.tv where you can find the discord, calendar and more!",
        ];
    }

    private function getFarewell() {
        if ($this->channel === '#aiyekara') {
            return ["I hope to see you next time. Same bat-time, same bat-channel ;)"];
        }

        return [];
    }

    /**
     * @param string $pattern
     *
     * @return bool
     */
    private function match(string $pattern): bool
    {
        return (boolean) preg_match("/{$pattern}/i", $this->text);
    }
}
