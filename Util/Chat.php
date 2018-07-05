<?php

namespace TwitchBot\Util;

require_once __DIR__.'/EventManager.php';
require_once __DIR__.'/Message.php';

use TwitchBot\Util\EventManager;
use TwitchBot\Util\Message;

class Chat
{
    /** @var \Resource */
    private $socket;

    /** @var boolean */
    private $debug = false;

    /** @var string */
    private $nick = null;

    /**
     * @param boolean $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return $this->nick;
    }

    /**
     * @return void
     */
    public function initializeSocket()
    {
        if ($this->debug) {
            echo " --- Initialized Socket\n";
        }
        $this->socket = fsockopen('irc.twitch.tv', 6667);
        stream_set_timeout($this->socket, 5);
    }

    /**
     * @param string $nick
     * @param string $token
     *
     * @return void
     */
    public function connect($nick, $token)
    {
        $this->nick = $nick;
        $this->send("PASS oauth:{$token}");
        $this->send("NICK {$nick}");
        $this->waitFor(376);

        if ($this->debug) {
            echo " --- Logged in as {$nick}\n";
        }
    }

    /**
     * @param string $channel
     *
     * @return void
     */
    public function join($channel)
    {
        $this->send("JOIN #{$channel}");
        $this->waitFor(365);

        if ($this->debug) {
            echo " --- Joined channel {$channel}\n";
        }
    }

    /**
     * @param string $data
     *
     * @return void
     */
    public function send($data)
    {
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
    public function sendMessage($channel, $message)
    {
        $this->send("PRIVMSG {$channel} :{$message}");
    }

    /**
     * @param string $command
     *
     * @return void
     */
    public function waitFor($command)
    {
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
     * @param string $command
     * @param EventManager $eventManager
     *
     * @return void
     */
    public function eventLoop($channel, EventManager $eventManager)
    {
        while (!feof($this->socket)) {
            $data = fgets($this->socket);

            if (!$data) {
                continue;
            }

            $message = new Message($data);

            if ($this->debug) {
                echo " <-- {$message->getRaw()}";
            }

            if ($message->isMalformed()) {
                continue;
            }

            $eventManager->emit($message->getCommand(), array($message));
        }
    }

    /**
     * @return void
     */
    public function closeSocket()
    {
        fclose($this->socket);
    }
}
