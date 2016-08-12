<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Bundle\DependencyInjection\Compiler;

use Novuso\Common\Domain\Messaging\Event\EventSubscriber;
use Novuso\System\Exception\RuntimeException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * EventSubscriberCompilerPass registers event subscribers with the dispatcher
 *
 * @copyright Copyright (c) 2016, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class EventSubscriberCompilerPass implements CompilerPassInterface
{
    /**
     * Processes event subscriber tags
     *
     * @param ContainerBuilder $container The container builder
     *
     * @return void
     *
     * @throws RuntimeException When a subscriber definition is not valid
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('novuso_common.event_dispatcher')) {
            return;
        }

        $definition = $container->findDefinition('novuso_common.event_dispatcher');
        $taggedServices = $container->findTaggedServiceIds('novuso_common.event_subscriber');

        foreach (array_keys($taggedServices) as $id) {
            $def = $container->getDefinition($id);

            if (!$def->isPublic()) {
                $message = sprintf('The service "%s" must be public as subscribers are lazy-loaded', $id);
                throw new RuntimeException($message);
            }

            if ($def->isAbstract()) {
                $message = sprintf('The service "%s" must not be abstract as subscribers are lazy-loaded', $id);
                throw new RuntimeException($message);
            }

            $class = $container->getParameterBag()->resolveValue($def->getClass());
            $refClass = new ReflectionClass($class);

            if (!$refClass->implementsInterface(EventSubscriber::class)) {
                $message = sprintf('Service "%s" must implement the interface "%s"', $id, EventSubscriber::class);
                throw new RuntimeException($message);
            }

            $definition->addMethodCall('registerService', [$class, $id]);
        }
    }
}
