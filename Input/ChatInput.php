<?php declare(strict_types=1);

namespace TwitchBot\Input;

use TwitchBot\Chat\Chat;

class ChatInput implements InputInterface {
    /** @var Chat */
    private $chat;

    /** @const string */
    const TYPE = 'CHAT';

    /**
     * @param Chat $chat
     *
     * @return void
     */
    public function __construct(Chat $chat) {
        $this->chat = $chat;
    }

    /**
     * @return string JSON
     */
    public function ask(): ?string {
        $message = $this->chat->fetch();

        if ($message === null) {
            return null;
        }

        $result = [
            'command' => $message->getCommand(),
            'params' => $message->getParams(),
            'prefix' => $message->getPrefix(),
            'raw' => $message->getRaw(),
            'content' => '',
        ];

        // Preprocessing normal chat messages
        if ($message->getCommand() === 'PRIVMSG') {
            /** @var string */
            $prefix = $message->getPrefix();
            list($user,) = explode('!', $prefix);

            $result['user'] = $user;
            $result['channel'] = $message->getParams()[0];
            $result['content'] = $message->getParams()[1];
        }

        return json_encode($result) ?: null;
    }
}
