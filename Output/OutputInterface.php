<?php declare(strict_types=1);

namespace TwitchBot\Output;

interface OutputInterface
{
    /**
     * Takes the processed json and outputs it
     *
     * @param string $json
     * @param string $inJson Input for reference
     *
     * @return void
     */
    public function tell(string $json, string $inJson): void;
}
