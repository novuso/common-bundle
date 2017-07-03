<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Console\Symfony;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * OutputStyle provides output helpers for console commands
 *
 * @copyright Copyright (c) 2017, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class OutputStyle extends SymfonyStyle
{
    /**
     * Console output
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * Constructs OutputStyle
     *
     * @param InputInterface  $input  The input
     * @param OutputInterface $output The output
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        parent::__construct($input, $output);
    }

    /**
     * Checks if verbosity is quiet (-q)
     *
     * @return bool
     */
    public function isQuiet(): bool
    {
        return $this->output->getVerbosity() === OutputInterface::VERBOSITY_QUIET;
    }

    /**
     * Checks if verbosity is verbose (-v)
     *
     * @return bool
     */
    public function isVerbose(): bool
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }

    /**
     * Checks if verbosity is very verbose (-vv)
     *
     * @return bool
     */
    public function isVeryVerbose(): bool
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE;
    }

    /**
     * Checks if verbosity is debug (-vvv)
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG;
    }
}
