<?php declare(strict_types=1);

namespace TwitchBot\Command;

use Symfony\Component\Console\{
    Command\Command,
    Input\InputInterface,
    Input\InputArgument,
    Output\OutputInterface
};

use Ratchet;

use TwitchBot\Websocket\Broadcaster;

class BroadcastServerCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void {
        $this
            ->setName('twitch-bot:server')
            ->setDescription('Runs the websocket server');
    }

    /**
     * @param InputInterface $input $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void {
        $host = getenv('TWITCH_BOT_WS_HOSTNAME') ?: '127.0.0.1';
        $port = getenv('TWITCH_BOT_WS_PORT') ?: '8081';
        $app = new Ratchet\App($host, $port, '0.0.0.0');
        $app->route('/broadcast', new Broadcaster(), ['*']);
        $app->run();
    }
}
