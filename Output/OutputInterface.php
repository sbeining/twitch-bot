<?php declare(strict_types=1);

namespace TwitchBot\Output;

interface OutputInterface
{
    /**
     * Takes the processed json and outputs it
     *
     * @param string $json
     *
     * @return void
     */
    public function tell(string $json): void;
}
