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
     *
     * @return void
     */
    public function tell(string $json): void {
        $data = json_decode($json, true);

        if (isset($data['pokemon'])) {
            $this->output->writeln($this->transformPokemon($data));
        } else {
            $this->output->writeln($json);
        }
    }
}
