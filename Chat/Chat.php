<?php declare(strict_types=1);

namespace TwitchBot\Chat;

use TwitchBot\Chat\Message;

class Chat
{
    /** @var resource */
    private $socket;

    /** @var bool */
    private $debug = false;

    /** @var string */
    private $nick = null;

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug): void {
        $this->debug = $debug;
    }

    /**
     * @return string
     */
    public function getNick(): string {
        return $this->nick;
    }

    /**
     * @return void
     */
    public function initializeSocket(): void {
        if ($this->debug) {
            echo " --- Initialized Socket\n";
        }
        $socket = fsockopen('irc.twitch.tv', 6667);

        if ($socket === false) {
            throw new \Exception('Socket could not be established!');
        }

        $this->socket = $socket;
        stream_set_timeout($this->socket, 5);
    }

    /**
     * @param string $nick
     * @param string $token
     *
     * @return void
     */
    public function connect(string $nick, string $token): void {
        $this->nick = $nick;
        $this->send("PASS oauth:{$token}");
        $this->send("NICK {$nick}");
        $this->waitFor('376');

        if ($this->debug) {
            echo " --- Logged in as {$nick}\n";
        }
    }

    /**
     * @param string $channel
     *
     * @return void
     */
    public function join(string $channel): void {
        $this->send("JOIN #{$channel}");
        $this->waitFor('365');

        if ($this->debug) {
            echo " --- Joined channel {$channel}\n";
        }
    }

    /**
     * @param string $data
     *
     * @return void
     */
    public function send(string $data): void {
        fwrite($this->socket, $data . "\r\n");

        if ($this->debug) {
            echo " --> $data\n";
        }
    }

    /**
     * @param string $channel
     * @param string $message
     *
     * @return void
     */
    public function sendMessage(string $channel, string $message): void {
        $this->send("PRIVMSG {$channel} :{$message}");
    }

    /**
     * @param string $command
     *
     * @return void
     */
    public function waitFor(string $command): void {
        while ($data = fgets($this->socket)) {
            $message = new Message($data);
            if ($this->debug) {
                echo " <-- {$message->getRaw()}";
            }
            if ($message->getCommand() == $command) {
                break;
            }
        }
    }

    /**
     * @return Message|null
     */
    public function fetch(): ?Message {
        if ($this->isSocketClosed()) {
            return null;
        }

        $data = fgets($this->socket);

        if (!$data) {
            return null;
        }

        $message = new Message($data);

        if ($this->debug) {
            echo " <-- {$message->getRaw()}";
        }

        if ($message->isMalformed()) {
            return null;
        }

        return $message;
    }

    /**
     * @return bool
     */
    public function isSocketClosed(): bool {
        return feof($this->socket);
    }

    /**
     * @return void
     */
    public function closeSocket() {
        fclose($this->socket);
    }
}
