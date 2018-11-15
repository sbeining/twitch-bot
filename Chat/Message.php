<?php declare(strict_types=1);

namespace TwitchBot\Chat;

class Message
{
    /** @var string */
    private $raw;

    /** @var array */
    private $tags = [];

    /** @var string */
    private $prefix = null;

    /** @var string */
    private $command = null;

    /** @var array */
    private $params = [];

    /** @var bool */
    private $malformed = false;

    /**
     * @param string $data
     *
     * @return void
     */
    public function __construct($data) {
        $this->raw = $data;
        $this->malformed = !$this->parse(rtrim($data));
    }

    /**
     * @return string
     */
    public function getRaw(): string {
        return $this->raw;
    }

    /**
     * @return array
     */
    public function getTags(): array {
        return $this->tags;
    }

    /**
     * @return string|null
     */
    public function getPrefix(): ?string {
        return $this->prefix;
    }

    /**
     * @return string|null
     */
    public function getCommand(): ?string {
        return $this->command;
    }

    /**
     * @return array
     */
    public function getParams(): array {
        return $this->params;
    }

    /**
     * @return bool
     */
    public function isMalformed(): bool {
        return $this->malformed;
    }

    /**
     * @param string $data
     *
     * @return bool
     */
    private function parse(string $data): bool {
        $position = 0;
        $nextspace = 0;

        // Skip any trailing whitespace..
        while (ord($data[$position]) === 32) {
            $position++;
        }

        if (ord($data[$position]) === 58) {
            $nextspace = strpos($data, ' ', $position);

            // If there's nothing after the prefix, deem this message to be malformed.
            if ($nextspace === false) {
                return false;
            }

            $this->prefix = substr($data, $position + 1, $nextspace - $position - 1);
            $position = $nextspace + 1;

            // Skip any trailing whitespace..
            while (ord($data[$position]) === 32) {
                $position++;
            }
        }

        $nextspace = strpos($data, ' ', $position);

        // If there's no more whitespace left, extract everything from the
        // current position to the end of the string as the command..
        if ($nextspace === false) {
            if (strlen($data) > $position) {
                $this->command = substr($data, $position);
                return true;
            }

            return false;
        }

        $this->command = substr($data, $position, $nextspace - $position);

        $position = $nextspace + 1;

        // Skip any trailing whitespace..
        while (ord($data[$position]) === 32) {
            $position++;
        }

        while ($position < strlen($data)) {
            $nextspace = strpos($data, ' ', $position);

            if (ord($data[$position]) === 58) {
                $this->params[] = substr($data, $position + 1);
                break;
            }

            if ($nextspace !== false) {
                $this->params[] = substr($data, $position, $nextspace - $position);
                $position = $nextspace + 1;

                // Skip any trailing whitespace..
                while (ord($data[$position]) === 32) {
                    $position++;
                }

                continue;
            }

            if ($nextspace === false) {
                $this->params[] = substr($data, $position);
                break;
            }
        }

        return true;
    }
}
