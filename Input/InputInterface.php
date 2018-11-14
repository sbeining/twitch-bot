<?php declare(strict_types=1);

namespace TwitchBot\Input;

interface InputInterface
{
    /**
     * Builds a json with information from the input
     * This may block to wait for an input or return null if
     * nothing was input yet
     *
     * @return string|null
     */
    public function ask(): ?string;
}
