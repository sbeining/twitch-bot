<?php declare(strict_types=1);

namespace TwitchBot\Input;

use Symfony\Component\Console\{
    Input\InputInterface AS ConsoleInput,
    Output\OutputInterface AS ConsoleOutput,
    Helper\QuestionHelper,
    Question\Question
};

class CliInput implements InputInterface {
    /** @var ConsoleInput */
    private $input;

    /** @var ConsoleOutput */
    private $output;

    /** @var QuestionHelper */
    private $helper;

    /** @var Question */
    private $prompt;

    /** @const string */
    const TYPE = 'CLI';

    /**
     * @param ConsoleInput $input
     * @param ConsoleOutput $output
     * @param QuestionHelper $helper
     *
     * @return void
     */
    public function __construct(
        ConsoleInput $input,
        ConsoleOutput $output,
        QuestionHelper $helper
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->helper = $helper;
        $this->prompt = new Question('> ');
    }

    /**
     * @return string JSON
     */
    public function ask(): ?string {
        $raw = $this->helper->ask($this->input, $this->output, $this->prompt);

        return json_encode([
            'origin' => self::TYPE,
            'content' => $raw,
        ]) ?: null;
    }
}
