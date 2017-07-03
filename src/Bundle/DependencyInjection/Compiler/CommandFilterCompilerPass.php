<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * CommandFilterCompilerPass registers filters with the command pipeline
 *
 * @copyright Copyright (c) 2017, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class CommandFilterCompilerPass implements CompilerPassInterface
{
    /**
     * Processes command filter tags
     *
     * @param ContainerBuilder $container The container builder
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('novuso_common.command_pipeline')) {
            return;
        }

        $definition = $container->findDefinition('novuso_common.command_pipeline');
        $taggedServices = $container->findTaggedServiceIds('novuso_common.command_filter');

        foreach (array_keys($taggedServices) as $id) {
            $definition->addMethodCall('addFilter', [new Reference($id)]);
        }
    }
}
