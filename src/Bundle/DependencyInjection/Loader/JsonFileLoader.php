<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Bundle\DependencyInjection\Loader;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * JsonFileLoader is a JSON file loader for the dependency injection component
 *
 * @copyright Copyright (c) 2016, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class JsonFileLoader extends YamlFileLoader
{
    /**
     * Checks if this class supports the given resource
     *
     * @param mixed       $resource A resource
     * @param string|null $type     The resource type or null if unknown
     *
     * @return bool
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource)
            && pathinfo($resource, PATHINFO_EXTENSION) === 'json'
            && (!$type || 'json' === $type);
    }

    /**
     * Loads a JSON file
     *
     * @param string $file The file path
     *
     * @return array
     *
     * @throws InvalidArgumentException When the file is not local
     */
    protected function loadFile($file)
    {
        if (!stream_is_local($file)) {
            $message = sprintf('This is not a local file "%s"', $file);
            throw new InvalidArgumentException($message);
        }

        if (!file_exists($file)) {
            $message = sprintf('The service file "%s" is not valid', $file);
            throw new InvalidArgumentException($message);
        }

        $config = json_decode(file_get_contents($file), true);

        // empty file
        if (null === $config) {
            return $config;
        }

        // not an array
        if (!is_array($config)) {
            $message = sprintf(
                'The service file "%s" is not valid. It should contain an array. Check your JSON syntax.',
                $file
            );
            throw new InvalidArgumentException($message);
        }

        foreach ($config as $namespace => $data) {
            if (in_array($namespace, ['imports', 'parameters', 'services'])) {
                continue;
            }
            if (!$this->container->hasExtension($namespace)) {
                $extensionNamespaces = array_filter(array_map(function ($ext) {
                    /** @var ExtensionInterface $ext */
                    return $ext->getAlias();
                }, $this->container->getExtensions()));
                $format = 'There is no extension able to load the configuration for "%s" (in %s). '
                    .'Looked for namespace "%s", found %s';
                $message = sprintf(
                    $format,
                    $namespace,
                    $file,
                    $namespace,
                    $extensionNamespaces ? sprintf('"%s"', implode('", "', $extensionNamespaces)) : 'none'
                );
                throw new InvalidArgumentException($message);
            }
        }

        return $config;
    }
}
