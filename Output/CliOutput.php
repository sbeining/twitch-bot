<?php declare(strict_types=1);

namespace TwitchBot\Output;

use Symfony\Component\Console\{
    Input\InputInterface AS ConsoleInput,
    Output\OutputInterface AS ConsoleOutput,
    Helper\QuestionHelper,
    Question\Question
};

use TwitchBot\Output\OutputTraits\PokemonTextOutput;

class CliOutput implements OutputInterface {
    use PokemonTextOutput;

    /** @var ConsoleOutput */
    private $output;

    /**
     * @param ConsoleOutput $output
     *
     * @return void
     */
    public function __construct(
        ConsoleOutput $output
    ) {
        $this->output = $output;
    }

    /**
     * @param string $json
     * @param string $inJson Input for reference
     *
     * @return void
     */
    public function tell(string $json, string $inJson): void {
        $data = json_decode($json, true);

        if (isset($data['pokemon'])) {
            $this->output->writeln($this->transformPokemon($data));
        } else {
            $this->output->writeln($json);
        }
    }
}
