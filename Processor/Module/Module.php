<?php declare(strict_types=1);

namespace TwitchBot\Processor\Module;

abstract class Module
{
    /**
     * @param string $in
     *
     * @return string|null
     */
    abstract public function handle(string $in): ?string;
}
