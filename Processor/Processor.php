<?php declare(strict_types=1);

namespace TwitchBot\Processor;

use TwitchBot\Input\InputInterface;
use TwitchBot\Output\OutputInterface;

use TwitchBot\Processor\Module\{
    Module
};

class Processor
{
    /** @var InputInterface */
    private $input;

    /** @var array<OutputInterface> */
    private $outputs = array();

    /** @var array<Module> */
    private $modules = array();

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->outputs[] = $output;
    }

    /**
     * @param OutputInterface $output
     *
     * @return $this
     */
    public function andOutput(OutputInterface $output): Processor
    {
        $this->outputs[] = $output;

        return $this;
    }

    /**
     * @param Module $module
     *
     * @return $this
     */
    public function withModule(Module $module): Processor
    {
        $this->modules[] = $module;

        return $this;
    }

    /**
     * @return void
     */
    public function process(): void
    {
        $in = $this->input->ask();

        if ($in === null) {
            return;
        }

        foreach ($this->modules as $module) {
            $out = $module->handle($in);

            if ($out === null) {
                continue;
            }

            foreach ($this->outputs as $output) {
                $output->tell($out);
            }
        }
    }
}
