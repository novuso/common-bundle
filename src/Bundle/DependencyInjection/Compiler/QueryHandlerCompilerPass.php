<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Bundle\DependencyInjection\Compiler;

use Novuso\Common\Domain\Messaging\Query\QueryHandler;
use Novuso\System\Exception\RuntimeException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * QueryHandlerCompilerPass registers query handlers with the service map
 *
 * @copyright Copyright (c) 2016, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class QueryHandlerCompilerPass implements CompilerPassInterface
{
    /**
     * Processes query handler tags
     *
     * @param ContainerBuilder $container The container builder
     *
     * @return void
     *
     * @throws RuntimeException When a query handler definition is not valid
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('novuso_common.query_service_map')) {
            return;
        }

        $definition = $container->findDefinition('novuso_common.query_service_map');
        $taggedServices = $container->findTaggedServiceIds('novuso_common.query_handler');

        foreach ($taggedServices as $id => $tags) {
            $def = $container->getDefinition($id);

            if (!$def->isPublic()) {
                $message = sprintf('The service "%s" must be public as query handlers are lazy-loaded', $id);
                throw new RuntimeException($message);
            }

            if ($def->isAbstract()) {
                $message = sprintf('The service "%s" must not be abstract as query handlers are lazy-loaded', $id);
                throw new RuntimeException($message);
            }

            $class = $container->getParameterBag()->resolveValue($def->getClass());
            $refClass = new ReflectionClass($class);

            if (!$refClass->implementsInterface(QueryHandler::class)) {
                $message = sprintf('Service "%s" must implement interface "%s"', $id, QueryHandler::class);
                throw new RuntimeException($message);
            }

            foreach ($tags as $attributes) {
                if (!isset($attributes['query'])) {
                    $message = sprintf('Service "%s" is missing query attribute', $id);
                    throw new RuntimeException($message);
                }
                $definition->addMethodCall('registerHandler', [$attributes['query'], $id]);
            }
        }
    }
}
