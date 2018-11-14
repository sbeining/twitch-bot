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

    /**
     * @param array $words
     *
     * @return string
     */
    protected function enumerate(array $words): string {
        $result = '';
        $cnt = 0;
        $max = count($words);

        foreach ($words as $name) {
            $result .= "{$name}";

            $cnt++;

            if ($cnt === $max) {
                continue;
            } else if ($cnt === $max - 1) {
                $result .= ' and ';
            } else {
                $result .= ', ';
            }
        }

        return $result;
    }
}
