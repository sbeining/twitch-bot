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
use TwitchBot\Websocket\SecureApp;

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
        $sslCert = getenv('TWITCH_BOT_SSL_CERT');
        $sslPk = getenv('TWITCH_BOT_SSL_PK');

        if ($sslCert && $sslPk) {
            $app = new SecureApp($host, $port, '0.0.0.0', null, [
                'local_cert' => $sslCert,
                'local_pk' => $sslPk,
            ]);
        } else {
            $app = new Ratchet\App($host, $port, '0.0.0.0');
        }

        $app->route('/broadcast', new Broadcaster(), ['*']);
        $app->run();
    }
}
