<?php declare(strict_types=1);

namespace TwitchBot\Command;

use Symfony\Component\Console\{
    Command\Command,
    Input\InputInterface,
    Output\OutputInterface
};

class RunCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void {
        $this
            ->setName('twitch-bot:run')
            ->setDescription('Runs the twitch bot');
    }

    /**
     * @param InputInterface $input $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void {
    }
}
