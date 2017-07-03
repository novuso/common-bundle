<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Bundle\DependencyInjection;

use Novuso\Common\Adapter\Bundle\DependencyInjection\Loader\JsonFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * NovusoCommonExtension loads services for Novuso Common
 *
 * @copyright Copyright (c) 2017, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class NovusoCommonExtension extends Extension
{
    /**
     * Loads container services and settings
     *
     * @param array            $configs   An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $fileLocator = new FileLocator(dirname(__DIR__).'/Resources/config');
        $loader = new JsonFileLoader($container, $fileLocator);
        $loader->load('services.json');
    }
}
