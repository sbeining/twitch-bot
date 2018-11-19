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
                'content' => implode("\n", [
                    "With pleasure @{$this->user} :)",
                    "Do you want to hang out with us when we are not streaming. Consider joining our Discord: https://discord.gg/XEzEEW5",
                    "Do you like hunting monsters? Do you think that it always comes back to dildos? Give Keldamist a visit! http://twitch.tv/keldamist",
                    "Do you prefer monsters of the smaller variety? Do you want to have a nifty keen time? Try WhoaMattBerry! http://twitch.tv/whoamattberry",
                    "Do you like everything from flipping houses to flat plumbers? Do you sometimes not know what's the point (and click)? Mew is there to help! http://twitch.tv/bretmwxyz",
                    "I hope to see you next time. Same bat-time, same bat-channel ;)"
                ])
            ]) ?: null;
        }

        return null;
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
