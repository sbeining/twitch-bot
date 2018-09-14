<?php

namespace TwitchBot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use TwitchBot\Listener\ConversationListener;
use TwitchBot\Listener\LogListener;
use TwitchBot\Listener\PokemonListener;
use TwitchBot\Listener\PokemonTypeListener;
use TwitchBot\Listener\PokemonNatureListener;
use TwitchBot\Listener\DeathCounterListener;
use TwitchBot\Listener\BossesKilledListener;
use TwitchBot\Listener\TodoListener;
use TwitchBot\Listener\PingPongListener;
use TwitchBot\Util\Chat\Chat;
use TwitchBot\Util\EventManager;
use TwitchBot\Util\Persistence;

class RunCommand extends Command
{
    /** @var EventManager */
    private $eventManager;


    /**
     * @return void
     */
    protected function configure()
    {
      $this
        ->setName('twitch-bot:run')
        ->setDescription('Runs the twitch bot')
        ->addArgument('token', InputArgument::REQUIRED, 'OAuthToken')
        ->addArgument('nick', InputArgument::REQUIRED, 'Nickname')
        ->addArgument('channel', InputArgument::REQUIRED, 'Channel');

      $this->persistence = new Persistence(__DIR__.'/../db.json');
    }

    /**
     * @param InputInterface $input $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $chat = new Chat();
        $eventManager = new EventManager();
        $eventManager->addListener(new ConversationListener($chat));
        $eventManager->addListener(new LogListener($chat));
        $eventManager->addListener(new PokemonListener($chat));
        $eventManager->addListener(new PokemonTypeListener($chat));
        $eventManager->addListener(new PokemonNatureListener($chat));
        $eventManager->addListener(new DeathCounterListener($chat, $this->persistence));
        $eventManager->addListener(new BossesKilledListener($chat, $this->persistence));
        $eventManager->addListener(new TodoListener($chat, $this->persistence));
        $eventManager->addListener(new PingPongListener($chat));

        $token = $input->getArgument('token');
        $nick = $input->getArgument('nick');
        $channel = $input->getArgument('channel');

        $chat->initializeSocket();

        $chat->connect($nick, $token);
        $chat->join($channel);
        $chat->eventLoop($channel, $eventManager);

        $chat->closeSocket();
    }
}
