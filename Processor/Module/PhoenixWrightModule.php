<?php declare(strict_types=1);

namespace TwitchBot\Processor\Module;

class PhoenixWrightModule extends Module {
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

        if ($this->match("!objection")) {
            return json_encode([
                'animation' => "phoenix|objection",
            ]) ?: null;
        }

        if ($this->match("!holdit")) {
            return json_encode([
                'animation' => "phoenix|holdit",
            ]) ?: null;
        }

        if ($this->match("!takethat")) {
            return json_encode([
                'animation' => "phoenix|takethat",
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
