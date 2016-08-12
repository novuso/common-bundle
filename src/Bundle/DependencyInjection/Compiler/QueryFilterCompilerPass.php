<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * QueryFilterCompilerPass registers filters with the query pipeline
 *
 * @copyright Copyright (c) 2016, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class QueryFilterCompilerPass implements CompilerPassInterface
{
    /**
     * Processes query filter tags
     *
     * @param ContainerBuilder $container The container builder
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('novuso_common.query_pipeline')) {
            return;
        }

        $definition = $container->findDefinition('novuso_common.query_pipeline');
        $taggedServices = $container->findTaggedServiceIds('novuso_common.query_filter');

        foreach (array_keys($taggedServices) as $id) {
            $definition->addMethodCall('addFilter', [new Reference($id)]);
        }
    }
}
