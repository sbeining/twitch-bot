<?php declare(strict_types=1);

namespace TwitchBot\Command;

use Symfony\Component\Console\{
    Command\Command,
    Input\InputInterface,
    Output\OutputInterface
};

use TwitchBot\Input\CliInput;
use TwitchBot\Processor\Processor;
use TwitchBot\Processor\Module\EchoModule;
use TwitchBot\Output\CliOutput;

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
        $in = new CliInput($input, $output, $this->getHelper('question'));
        $out = new CliOutput($output);

        $processor = new Processor($in, $out);
        $processor->withModule(new EchoModule());

        while(true) {
            $processor->process();
        }
    }
}
