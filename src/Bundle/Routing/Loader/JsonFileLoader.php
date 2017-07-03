<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Bundle\Routing\Loader;

use InvalidArgumentException;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;

/**
 * JsonFileLoader is a JSON file loader for the routing component
 *
 * @copyright Copyright (c) 2017, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class JsonFileLoader extends YamlFileLoader
{
    /**
     * Loads a JSON file
     *
     * @param string      $file A JSON file path
     * @param string|null $type The resource type
     *
     * @return RouteCollection
     *
     * @throws InvalidArgumentException When the JSON is invalid
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        if (!stream_is_local($path)) {
            $message = sprintf('This is not a local file "%s"', $path);
            throw new InvalidArgumentException($message);
        }

        if (!file_exists($path)) {
            $message = sprintf('File "%s" not found', $path);
            throw new InvalidArgumentException($message);
        }

        $parsedConfig = json_decode(file_get_contents($path), true);
        $collection = new RouteCollection();
        $collection->addResource(new FileResource($path));

        // empty file
        if ($parsedConfig === null) {
            return $collection;
        }

        // not an array
        if (!is_array($parsedConfig)) {
            $message = sprintf('The file "%s" must contain a JSON object', $path);
            throw new InvalidArgumentException($message);
        }

        foreach ($parsedConfig as $name => $config) {
            $this->validate($config, $name, $path);

            if (isset($config['resource'])) {
                $this->parseImport($collection, $config, $path, $file);
            } else {
                $this->parseRoute($collection, $name, $config, $path);
            }
        }

        return $collection;
    }

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
}
