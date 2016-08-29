<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Bundle;

use Novuso\Common\Adapter\Bundle\DependencyInjection\Compiler\CommandFilterCompilerPass;
use Novuso\Common\Adapter\Bundle\DependencyInjection\Compiler\CommandHandlerCompilerPass;
use Novuso\Common\Adapter\Bundle\DependencyInjection\Compiler\EventSubscriberCompilerPass;
use Novuso\Common\Adapter\Bundle\DependencyInjection\Compiler\QueryFilterCompilerPass;
use Novuso\Common\Adapter\Bundle\DependencyInjection\Compiler\QueryHandlerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * NovusoCommonBundle is the Symfony Bundle for Novuso Common
 *
 * @copyright Copyright (c) 2016, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class NovusoCommonBundle extends Bundle
{
    /**
     * Boots the bundle
     *
     * @return void
     */
    public function boot()
    {
        if ($this->container->has('doctrine')) {
            // register custom data types
        }
    }

    /**
     * Builds in container modifications when cache is empty
     *
     * @param ContainerBuilder $container The container builder
     *
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new CommandFilterCompilerPass());
        $container->addCompilerPass(new CommandHandlerCompilerPass());
        $container->addCompilerPass(new EventSubscriberCompilerPass());
        $container->addCompilerPass(new QueryFilterCompilerPass());
        $container->addCompilerPass(new QueryHandlerCompilerPass());
    }
}
