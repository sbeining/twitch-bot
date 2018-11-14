<?php declare(strict_types=1);

namespace TwitchBot\Processor\Module;

class EchoModule extends Module
{
    /**
     * @param string $in
     *
     * @return string|null
     */
    public function handle(string $in): ?string {
        return $in;
    }
}
