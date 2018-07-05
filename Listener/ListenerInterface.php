<?php

namespace TwitchBot\Listener;

interface ListenerInterface
{
    /**
     * @return array
     */
    public function listensFor(): array;

    /**
     * @param mixed $payload
     *
     * @return void
     */
    public function execute($payload);
}
