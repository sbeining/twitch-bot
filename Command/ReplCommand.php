<?php

namespace TwitchBot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use TwitchBot\Listener\PokemonListener;
use TwitchBot\Listener\PokemonTypeListener;
use TwitchBot\Listener\DeathCounterListener;
use TwitchBot\Listener\BossesKilledListener;
use TwitchBot\Util\Chat\CliChat;
use TwitchBot\Util\EventManager;
use TwitchBot\Util\Message;
use TwitchBot\Util\Persistence;

class ReplCommand extends Command
{
    /** @var EventManager */
    private $eventManager;


    /**
     * @return void
     */
    protected function configure()
    {
      $this
        ->setName('twitch-bot:repl')
        ->setDescription('Offers an interactive shell to directly use/test bot commands');

      $this->persistence = new Persistence(__DIR__.'/../db_repl.json');
    }

    /**
     * @param InputInterface $input $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $chat = new CliChat();

        $eventManager = new EventManager();
        $eventManager->addListener(new PokemonListener($chat));
        $eventManager->addListener(new PokemonTypeListener($chat));
        $eventManager->addListener(new DeathCounterListener($chat, $this->persistence));
        $eventManager->addListener(new BossesKilledListener($chat, $this->persistence));

        $output->writeln('You can test chat commands here. Type "exit" to exit.');
        $helper = $this->getHelper('question');
        $question = new Question('> ');

        $command = null;

        while ($command !== 'exit') {
            $command = $helper->ask($input, $output, $question);
            $eventManager->emit('PRIVMSG', array($this->buildMessage($command)));
        }
    }

    /**
     * @param string $command
     *
     * @return Message
     */
    private function buildMessage($command)
    {
        $input = ":cli!cli@cli.tmi.twitch.tv PRIVMSG #channel :{$command}";

        return new Message($input);
    }
}
