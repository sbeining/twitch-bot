<?php

namespace TwitchBot\Processor\Module;

class PingPongModule extends Module
{
    /**
     * @param string $in
     *
     * @return string|null
     */
    public function handle(string $in): ?string {
        $data = json_decode($in, true);

        if ($data['command'] !== 'PING') {
            return null;
        }

        return json_encode([
            'command' => 'PONG',
            'params' => $data['params'],
            'prefix' => $data['prefix'],
            'raw' => "PONG :{$data['params'][0]}",
        ]) ?: null;
    }
}
