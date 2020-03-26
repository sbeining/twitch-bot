<?php declare(strict_types=1);

namespace TwitchBot\Command;

use Symfony\Component\Console\{
    Command\Command,
    Input\InputInterface,
    Input\InputArgument,
    Output\OutputInterface
};

use TwitchBot\Chat\Chat;
use TwitchBot\Input\{
    CliInput,
    ChatInput
};
use TwitchBot\Processor\Processor;
use TwitchBot\Processor\Module\{
    ConversationModule,
    PingPongModule,
    PokemonModule,
    PokemonNatureModule,
    PokemonTypeModule,
    PhoenixWrightModule,
    PokemonRaidModule
};
use TwitchBot\Output\{
    CliOutput,
    ChatOutput,
    WebsocketOutput
};

class RunCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void {
        $this
            ->setName('twitch-bot:run')
            ->setDescription('Runs the twitch bot')
            ->addArgument('token', InputArgument::REQUIRED, 'OAuthToken')
            ->addArgument('nick', InputArgument::REQUIRED, 'Nickname')
            ->addArgument('channel', InputArgument::REQUIRED, 'Channel');
    }

    /**
     * @param InputInterface $input $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void {
        /** @var string */
        $token = $input->getArgument('token');
        /** @var string */
        $nick = $input->getArgument('nick');
        /** @var string */
        $channel = $input->getArgument('channel');

        $chat = new Chat();
        $chat->initializeSocket();
        $chat->connect($nick, $token);
        $chat->join($channel);

        $in = new ChatInput($chat);
        $out = new ChatOutput($chat, '#' . $channel);

        $wsUrl = getenv('TWITCH_BOT_WS') ?: 'ws://127.0.0.1:8081/broadcast';
        $websocketOut = new WebsocketOutput($wsUrl);

        $processor = new Processor($in, $out);
        $processor->andOutput($websocketOut);
        $processor
            ->withModule(new ConversationModule())
            ->withModule(new PingPongModule())
            ->withModule(new PokemonModule())
            ->withModule(new PokemonNatureModule())
            ->withModule(new PokemonTypeModule())
            ->withModule(new PhoenixWrightModule())
            ->withModule(new PokemonRaidModule());

        while(true) {
            $processor->process();
        }
    }
}
