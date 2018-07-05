<?php

namespace TwitchBot\Command;

require_once __DIR__.'/../Listener/PingPongListener.php';
require_once __DIR__.'/../Util/Chat.php';
require_once __DIR__.'/../Util/EventManager.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use TwitchBot\Listener\PingPongListener;
use TwitchBot\Util\Chat;
use TwitchBot\Util\EventManager;

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
