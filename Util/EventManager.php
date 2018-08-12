<?php

namespace TwitchBot\Util;

use TwitchBot\Listener\ListenerInterface;

class EventManager
{
    /** @var $listeners */
    private $listeners = [];

    /**
     * @param ListenerInterface $listener
     *
     * @return void
     */
    public function addListener(ListenerInterface $listener)
    {
        foreach ($listener->listensFor() as $event) {
            $this->listeners[$event][] = $listener;
        }
    }

    /**
     * @param string $event
     * @param mixed $payload
     *
     * @return void
     */
    public function emit($event, $payload)
    {
        if (!isset($this->listeners[$event])) {
            return;
        }

        foreach ($this->listeners[$event] as $listener) {
            $listener->execute($payload);
        }
    }
}
