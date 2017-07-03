<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Console\Symfony;

use Symfony\Component\Console\Application as Console;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Debug\Debug;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Application is the entry point for a console application
 *
 * @copyright Copyright (c) 2017, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class Application extends Console
{
    /**
     * Runtime environment
     *
     * @var string
     */
    protected $environment;

    /**
     * Debug status
     *
     * @var bool
     */
    protected $debug;

    /**
     * Constructs Application
     *
     * @param EventDispatcherInterface $dispatcher  The Symfony event dispatcher
     * @param string                   $name        The application name
     * @param string                   $version     The version
     * @param string                   $environment The environment
     * @param bool                     $debug       Whether to enable debug mode
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        string $name,
        string $version,
        string $environment,
        bool $debug
    ) {
        $this->environment = $environment;
        $this->debug = $debug;

        if ($this->debug) {
            Debug::enable();
        }

        parent::__construct($name, $version);

        $this->setDispatcher($dispatcher);

        $this->getDefinition()->addOption(
            new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The environment name')
        );
        $this->getDefinition()->addOption(
            new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switches off debug mode')
        );
    }

    /**
     * Retrieves the current environment
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Checks if debug mode is enabled
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }
}
