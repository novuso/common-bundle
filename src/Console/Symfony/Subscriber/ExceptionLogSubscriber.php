<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Console\Symfony\Subscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * ExceptionLogSubscriber logs exceptions from the console application
 *
 * @copyright Copyright (c) 2017, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class ExceptionLogSubscriber implements EventSubscriberInterface
{
    /**
     * Logger
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructs ExceptionLogSubscriber
     *
     * @param LoggerInterface $logger The logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Registers event subscription
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [ConsoleEvents::ERROR => 'onConsoleError'];
    }

    /**
     * Logs console exception
     *
     * @param ConsoleErrorEvent $event The event
     *
     * @return void
     */
    public function onConsoleException(ConsoleErrorEvent $event)
    {
        $exception = $event->getError();
        $exitCode = $event->getExitCode();
        $message = sprintf(
            '%s: "%s" at %s line %s',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        $this->logger->error($message, ['exit_code' => $exitCode, 'exception' => $exception]);
    }
}
