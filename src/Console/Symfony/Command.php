<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Console\Symfony;

use Novuso\System\Exception\RuntimeException;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command is the base class for commands
 *
 * @copyright Copyright (c) 2017, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class Command extends BaseCommand
{
    /**
     * Console input
     *
     * @var InputInterface
     */
    protected $input;

    /**
     * Standard output
     *
     * @var OutputStyle
     */
    protected $output;

    /**
     * Standard error
     *
     * @var OutputStyle
     */
    protected $stderr;

    /**
     * Command name
     *
     * @var string
     */
    protected $name;

    /**
     * Command description
     *
     * @var string
     */
    protected $description;

    /**
     * Progress bar
     *
     * @var ProgressBar|null
     */
    private $progressBar;

    /**
     * Constructs Command
     */
    public function __construct()
    {
        parent::__construct($this->name);
        $this->setDescription($this->description);
        $this->specifyParameters();
    }

    /**
     * Checks if enabled for the current environment
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return true;
    }

    /**
     * Retrieves the current environment
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        /** @var Application $application */
        $application = $this->getApplication();

        return $application->getEnvironment();
    }

    /**
     * Checks if debug is enabled
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        /** @var Application $application */
        $application = $this->getApplication();

        return $application->isDebug();
    }

    /**
     * Retrieves argument value
     *
     * @param string|null $key The key
     *
     * @return string|array
     */
    public function argument(string $key = null)
    {
        if ($key === null) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Retrieves option value
     *
     * @param string|null $key The key
     *
     * @return string|array
     */
    public function option(string $key = null)
    {
        if ($key === null) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * Writes a line of text as success output
     *
     * @param string $string The string
     * @param bool   $stdout Whether to write to stdout
     *
     * @return void
     */
    public function success(string $string, bool $stdout = false)
    {
        $this->writeln(sprintf('<success>%s</success>', $string), $stdout);
    }

    /**
     * Writes a line of text as comment output
     *
     * @param string $string The string
     * @param bool   $stdout Whether to write to stdout
     *
     * @return void
     */
    public function comment(string $string, bool $stdout = false)
    {
        $this->writeln(sprintf('<comment>%s</comment>', $string), $stdout);
    }

    /**
     * Writes a line of text as question output
     *
     * @param string $string The string
     * @param bool   $stdout Whether to write to stdout
     *
     * @return void
     */
    public function question(string $string, bool $stdout = false)
    {
        $this->writeln(sprintf('<question>%s</question>', $string), $stdout);
    }

    /**
     * Writes a line of text as info output
     *
     * @param string $string The string
     * @param bool   $stdout Whether to write to stdout
     *
     * @return void
     */
    public function info(string $string, bool $stdout = false)
    {
        $this->writeln(sprintf('<info>%s</info>', $string), $stdout);
    }

    /**
     * Writes a line of text as warning output
     *
     * @param string $string The string
     * @param bool   $stdout Whether to write to stdout
     *
     * @return void
     */
    public function warning(string $string, bool $stdout = false)
    {
        $this->writeln(sprintf('<warning>%s</warning>', $string), $stdout);
    }

    /**
     * Writes a line of text as error output
     *
     * @param string $string The string
     * @param bool   $stdout Whether to write to stdout
     *
     * @return void
     */
    public function error(string $string, bool $stdout = false)
    {
        $this->writeln(sprintf('<error>%s</error>', $string), $stdout);
    }

    /**
     * Writes a line of text as a success block
     *
     * @param string $string The string
     * @param bool   $stdout Whether to write to stdout
     *
     * @return void
     */
    public function successBlock(string $string, bool $stdout = false)
    {
        $this->block($string, 'OK', 'fg=white;bg=green', ' ', true, $stdout);
    }

    /**
     * Writes a line of text as a success block
     *
     * @param string $string The string
     * @param bool   $stdout Whether to write to stdout
     *
     * @return void
     */
    public function commentBlock(string $string, bool $stdout = false)
    {
        $this->block($string, null, 'fg=green;bg=black', ' // ', true, $stdout);
    }

    /**
     * Writes a line of text as a success block
     *
     * @param string $string The string
     * @param bool   $stdout Whether to write to stdout
     *
     * @return void
     */
    public function questionBlock(string $string, bool $stdout = false)
    {
        $this->block($string, '???', 'fg=black;bg=cyan', ' ', true, $stdout);
    }

    /**
     * Writes a line of text as a success block
     *
     * @param string $string The string
     * @param bool   $stdout Whether to write to stdout
     *
     * @return void
     */
    public function infoBlock(string $string, bool $stdout = false)
    {
        $this->block($string, 'INFO', 'fg=white;bg=blue', ' ', true, $stdout);
    }

    /**
     * Writes a line of text as a success block
     *
     * @param string $string The string
     * @param bool   $stdout Whether to write to stdout
     *
     * @return void
     */
    public function warningBlock(string $string, bool $stdout = false)
    {
        $this->block($string, 'WARNING', 'fg=black;bg=yellow', ' ', true, $stdout);
    }

    /**
     * Writes a line of text as a success block
     *
     * @param string $string The string
     * @param bool   $stdout Whether to write to stdout
     *
     * @return void
     */
    public function errorBlock(string $string, bool $stdout = false)
    {
        $this->block($string, 'ERROR', 'fg=white;bg=red', ' ', true, $stdout);
    }

    /**
     * Convenience method for STDOUT output
     *
     * @param string $string The output string
     *
     * @return void
     */
    public function stdout(string $string)
    {
        $this->write($string, true);
    }

    /**
     * Convenience method for STDERR output
     *
     * @param string $string The output string
     *
     * @return void
     */
    public function stderr(string $string)
    {
        $this->write($string, false);
    }

    /**
     * Writes text to output
     *
     * @param string $string The string
     * @param bool   $stdout Whether to write to stdout
     *
     * @return void
     */
    public function write(string $string, bool $stdout = true)
    {
        if ($stdout) {
            $this->output->write($string);
        } else {
            $this->stderr->write($string);
        }
    }

    /**
     * Writes a line of text
     *
     * @param string $string The string
     * @param bool   $stdout Whether to write to stdout
     *
     * @return void
     */
    public function writeln(string $string, bool $stdout = true)
    {
        if ($stdout) {
            $this->output->writeln($string);
        } else {
            $this->stderr->writeln($string);
        }
    }

    /**
     * Formats a string as a block of text
     *
     * @param string      $string   The string
     * @param string|null $type     The block type
     * @param string|null $style    The style to apply
     * @param string      $prefix   The prefix for the block
     * @param bool        $padding  Whether to add vertical padding
     * @param bool        $stdout   Whether to write to stdout
     *
     * @return void
     */
    public function block(
        string $string,
        string $type = null,
        string $style = null,
        string $prefix = ' ',
        bool $padding = false,
        bool $stdout = true
    ) {
        if ($stdout) {
            $this->output->block($string, $type, $style, $prefix, $padding);
        } else {
            $this->stderr->block($string, $type, $style, $prefix, $padding);
        }
    }

    /**
     * Formats data in a table
     *
     * @param array $headers The headers
     * @param array $rows    The rows
     * @param bool  $stdout  Whether to write to stdout
     *
     * @return void
     */
    public function table(array $headers, array $rows, bool $stdout = true)
    {
        if ($stdout) {
            $this->output->table($headers, $rows);
        } else {
            $this->stderr->table($headers, $rows);
        }
    }

    /**
     * Starts the progress bar output
     *
     * @param int  $max    The maximum steps or 0 if unknown
     * @param bool $stdout Whether to write to stdout
     *
     * @return void
     */
    public function progressStart(int $max = 0, bool $stdout = false)
    {
        if ($stdout) {
            $this->progressBar = $this->output->createProgressBar($max);
        } else {
            $this->progressBar = $this->stderr->createProgressBar($max);
        }

        $this->progressBar->start();
    }

    /**
     * Advances the progress bar
     *
     * @param int $step The number of steps to advance
     *
     * @return void
     */
    public function progressAdvance(int $step = 1)
    {
        $this->getProgressBar()->advance($step);
    }

    /**
     * Finishes the progress bar output
     *
     * @param bool $stdout Whether to write to stdout
     *
     * @return void
     */
    public function progressFinish(bool $stdout = false)
    {
        $this->getProgressBar()->finish();

        if ($stdout) {
            $this->output->newLine(2);
        } else {
            $this->stderr->newLine(2);
        }

        $this->progressBar = null;
    }

    /**
     * Asks a question
     *
     * @param string        $question  The question
     * @param string|null   $default   The default
     * @param callable|null $validator The validator
     * @param bool          $stdout    Whether to write to stdout
     *
     * @return string
     */
    public function ask(
        string $question,
        string $default = null,
        callable $validator = null,
        bool $stdout = true
    ): string {
        if ($stdout) {
            return $this->output->ask($question, $default, $validator);
        } else {
            return $this->stderr->ask($question, $default, $validator);
        }
    }

    /**
     * Asks a question with the input hidden
     *
     * @param string        $question  The question
     * @param callable|null $validator The validator
     * @param bool          $stdout    Whether to write to stdout
     *
     * @return string
     */
    public function secret(string $question, callable $validator = null, bool $stdout = true): string
    {
        if ($stdout) {
            return $this->output->askHidden($question, $validator);
        } else {
            return $this->stderr->askHidden($question, $validator);
        }
    }

    /**
     * Asks for confirmation
     *
     * @param string $question The question
     * @param bool   $default  The default
     * @param bool   $stdout   Whether to write to stdout
     *
     * @return bool
     */
    public function confirm(string $question, bool $default = true, bool $stdout = true): bool
    {
        if ($stdout) {
            return $this->output->confirm($question, $default);
        } else {
            return $this->stderr->confirm($question, $default);
        }
    }

    /**
     * Asks a choice question
     *
     * @param string      $question The question
     * @param array       $choices  A list of choices
     * @param string|null $default  The default
     * @param bool        $stdout   Whether to write to stdout
     *
     * @return string|null
     */
    public function choice(string $question, array $choices, string $default = null, bool $stdout = true)
    {
        if ($stdout) {
            return $this->output->choice($question, $choices, $default);
        } else {
            return $this->stderr->choice($question, $choices, $default);
        }
    }

    /**
     * Call another console command
     *
     * @param string $name      The command name
     * @param array  $arguments Command arguments
     *
     * @return int
     */
    public function call(string $name, array $arguments = []): int
    {
        $command = $this->getApplication()->find($name);
        $arguments['command'] = $name;

        return $command->run(new ArrayInput($arguments), $this->output);
    }

    /**
     * Call another console command silently
     *
     * @param string $name      The command name
     * @param array  $arguments Command arguments
     *
     * @return int
     */
    public function callSilent(string $name, array $arguments = []): int
    {
        $command = $this->getApplication()->find($name);
        $arguments['command'] = $name;

        return $command->run(new ArrayInput($arguments), new NullOutput());
    }

    /**
     * Runs the command
     *
     * @param InputInterface  $input  The input
     * @param OutputInterface $output The output
     *
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->setFormatterStyles($output);
        $this->input = $input;
        $this->output = new OutputStyle($input, $output);

        if ($output instanceof ConsoleOutputInterface) {
            $this->stderr = new OutputStyle($input, $output->getErrorOutput());
        } else {
            $this->stderr = new OutputStyle($input, $output);
        }

        return parent::run($input, $output);
    }

    /**
     * Specifies the arguments and options
     *
     * @return void
     */
    protected function specifyParameters()
    {
        collect($this->getArguments())->each(function ($argument) {
            call_user_func_array([$this, 'addArgument'], $argument);
        });
        collect($this->getOptions())->each(function ($option) {
            call_user_func_array([$this, 'addOption'], $option);
        });
    }

    /**
     * Executes the command
     *
     * @param InputInterface  $input  The input
     * @param OutputInterface $output The output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->fire();
    }

    /**
     * Fires the command
     *
     * @return int
     */
    protected function fire(): int
    {
        return 0;
    }

    /**
     * Retrieves the command arguments
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [];
    }

    /**
     * Retrieves the command options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [];
    }

    /**
     * Sets default formatter styles
     *
     * @param OutputInterface $output The output
     *
     * @return void
     */
    private function setFormatterStyles(OutputInterface $output)
    {
        $formatter = $output->getFormatter();
        $formatter->setStyle('success', new OutputFormatterStyle('green'));
        $formatter->setStyle('comment', new OutputFormatterStyle('cyan'));
        $formatter->setStyle('question', new OutputFormatterStyle('black', 'cyan'));
        $formatter->setStyle('info', new OutputFormatterStyle('blue'));
        $formatter->setStyle('warning', new OutputFormatterStyle('yellow'));
        $formatter->setStyle('error', new OutputFormatterStyle('red'));
    }

    /**
     * Retrieves the progress bar
     *
     * @return ProgressBar
     *
     * @throws RuntimeException When the progress bar is not started
     */
    private function getProgressBar(): ProgressBar
    {
        if (!$this->progressBar) {
            throw new RuntimeException('The ProgressBar is not started');
        }

        return $this->progressBar;
    }
}
