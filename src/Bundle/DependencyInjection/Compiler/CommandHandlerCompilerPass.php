<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Bundle\DependencyInjection\Compiler;

use Novuso\Common\Domain\Messaging\Command\CommandHandler;
use Novuso\System\Exception\RuntimeException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * CommandHandlerCompilerPass registers command handlers with the service map
 *
 * @copyright Copyright (c) 2017, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class CommandHandlerCompilerPass implements CompilerPassInterface
{
    /**
     * Processes command handler tags
     *
     * @param ContainerBuilder $container The container builder
     *
     * @return void
     *
     * @throws RuntimeException When a command handler definition is not valid
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('novuso_common.command_service_map')) {
            return;
        }

        $definition = $container->findDefinition('novuso_common.command_service_map');
        $taggedServices = $container->findTaggedServiceIds('novuso_common.command_handler');

        foreach ($taggedServices as $id => $tags) {
            $def = $container->getDefinition($id);

            if (!$def->isPublic()) {
                $message = sprintf('The service "%s" must be public as command handlers are lazy-loaded', $id);
                throw new RuntimeException($message);
            }

            if ($def->isAbstract()) {
                $message = sprintf('The service "%s" must not be abstract as command handlers are lazy-loaded', $id);
                throw new RuntimeException($message);
            }

            $class = $container->getParameterBag()->resolveValue($def->getClass());
            $refClass = new ReflectionClass($class);

            if (!$refClass->implementsInterface(CommandHandler::class)) {
                $message = sprintf('Service "%s" must implement interface "%s"', $id, CommandHandler::class);
                throw new RuntimeException($message);
            }

            foreach ($tags as $attributes) {
                if (!isset($attributes['command'])) {
                    $message = sprintf('Service "%s" is missing command attribute', $id);
                    throw new RuntimeException($message);
                }
                $definition->addMethodCall('registerHandler', [$attributes['command'], $id]);
            }
        }
    }
}
