<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Bundle\DependencyInjection\Loader;

use Exception;
use InvalidArgumentException;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;

/**
 * JsonFileLoader is a JSON file loader for the dependency injection component
 *
 * @copyright Copyright (c) 2016, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class JsonFileLoader extends FileLoader
{
    private static $keywords = [
        'alias'                 => 'alias',
        'parent'                => 'parent',
        'class'                 => 'class',
        'shared'                => 'shared',
        'synthetic'             => 'synthetic',
        'lazy'                  => 'lazy',
        'public'                => 'public',
        'abstract'              => 'abstract',
        'deprecated'            => 'deprecated',
        'factory'               => 'factory',
        'file'                  => 'file',
        'arguments'             => 'arguments',
        'properties'            => 'properties',
        'configurator'          => 'configurator',
        'calls'                 => 'calls',
        'tags'                  => 'tags',
        'decorates'             => 'decorates',
        'decoration_inner_name' => 'decoration_inner_name',
        'decoration_priority'   => 'decoration_priority',
        'autowire'              => 'autowire',
        'autowiring_types'      => 'autowiring_types',
    ];

    /**
     * Loads a JSON file
     *
     * @param mixed       $resource The resource
     * @param string|null $type     The resource type of null if unknown
     *
     * @throws Exception When an error occurs
     */
    public function load($resource, $type = null)
    {
        $path = $this->locator->locate($resource);

        $content = $this->loadFile($path);

        $this->container->addResource(new FileResource($path));

        // empty file
        if (null === $content) {
            return;
        }

        // imports
        $this->parseImports($content, $path);

        // parameters
        if (isset($content['parameters'])) {
            if (!is_array($content['parameters'])) {
                $message = sprintf(
                    'The "parameters" key should contain an array in %s. Check your JSON syntax',
                    $resource
                );
                throw new InvalidArgumentException($message);
            }
            foreach ($content['parameters'] as $key => $value) {
                $this->container->setParameter($key, $this->resolveServices($value));
            }
        }

        // extensions
        $this->loadFromExtensions($content);

        // services
        $this->parseDefinitions($content, $resource);
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

    /**
     * Parses all imports
     *
     * @param array  $content The content
     * @param string $file    The file path
     *
     * @return void
     *
     * @throws Exception When an error occurs
     */
    private function parseImports($content, $file)
    {
        if (!isset($content['imports'])) {
            return;
        }

        if (!is_array($content['imports'])) {
            $message = sprintf(
                'The "imports" key should contain an array in %s. Check your JSON syntax',
                $file
            );
            throw new InvalidArgumentException($message);
        }

        $defaultDirectory = dirname($file);
        foreach ($content['imports'] as $import) {
            if (!is_array($import)) {
                $message = sprintf(
                    'The values in the "imports" key should be arrays in %s. Check your JSON syntax',
                    $file
                );
                throw new InvalidArgumentException($message);
            }

            $this->setCurrentDir($defaultDirectory);
            $this->import(
                $import['resource'],
                null,
                isset($import['ignore_errors']) ? (bool) $import['ignore_errors'] : false,
                $file
            );
        }
    }

    /**
     * Parses definitions
     *
     * @param array  $content The content
     * @param string $file    The file path
     *
     * @return void
     *
     * @throws Exception When an error occurs
     */
    private function parseDefinitions($content, $file)
    {
        if (!isset($content['services'])) {
            return;
        }

        if (!is_array($content['services'])) {
            $message = sprintf(
                'The "services" key should contain an array in %s. Check your JSON syntax',
                $file
            );
            throw new InvalidArgumentException($message);
        }

        foreach ($content['services'] as $id => $service) {
            $this->parseDefinition($id, $service, $file);
        }
    }

    /**
     * Parses a definition
     *
     * @param string $id      The service ID
     * @param array  $service The service definition
     * @param string $file    The file path
     *
     * @throws InvalidArgumentException When tags are invalid
     */
    private function parseDefinition($id, $service, $file)
    {
        if (is_string($service) && 0 === strpos($service, '@')) {
            $this->container->setAlias($id, substr($service, 1));

            return;
        }

        if (!is_array($service)) {
            $message = sprintf(
                'A service definition must be an array or a string starting with "@" but %s found for '
                    .'service "%s" in %s. Check your JSON syntax',
                gettype($service),
                $id,
                $file
            );
            throw new InvalidArgumentException($message);
        }

        static::checkDefinition($id, $service, $file);

        if (isset($service['alias'])) {
            $public = !array_key_exists('public', $service) || (bool) $service['public'];
            $this->container->setAlias($id, new Alias($service['alias'], $public));

            return;
        }

        if (isset($service['parent'])) {
            $definition = new DefinitionDecorator($service['parent']);
        } else {
            $definition = new Definition();
        }

        if (isset($service['class'])) {
            $definition->setClass($service['class']);
        }

        if (isset($service['shared'])) {
            $definition->setShared($service['shared']);
        }

        if (isset($service['synthetic'])) {
            $definition->setSynthetic($service['synthetic']);
        }

        if (isset($service['lazy'])) {
            $definition->setLazy($service['lazy']);
        }

        if (isset($service['public'])) {
            $definition->setPublic($service['public']);
        }

        if (isset($service['abstract'])) {
            $definition->setAbstract($service['abstract']);
        }

        if (array_key_exists('deprecated', $service)) {
            $definition->setDeprecated(true, $service['deprecated']);
        }

        if (isset($service['factory'])) {
            $definition->setFactory($this->parseCallable($service['factory'], 'factory', $id, $file));
        }

        if (isset($service['file'])) {
            $definition->setFile($service['file']);
        }

        if (isset($service['arguments'])) {
            $definition->setArguments($this->resolveServices($service['arguments']));
        }

        if (isset($service['properties'])) {
            $definition->setProperties($this->resolveServices($service['properties']));
        }

        if (isset($service['configurator'])) {
            $definition->setConfigurator($this->parseCallable($service['configurator'], 'configurator', $id, $file));
        }

        if (isset($service['calls'])) {
            if (!is_array($service['calls'])) {
                $message = sprintf(
                    'Parameter "calls" must be an array for service "%s" in %s. Check your JSON syntax',
                    $id,
                    $file
                );
                throw new InvalidArgumentException($message);
            }

            foreach ($service['calls'] as $call) {
                if (isset($call['method'])) {
                    $method = $call['method'];
                    $args = isset($call['arguments']) ? $this->resolveServices($call['arguments']) : array();
                } else {
                    $method = $call[0];
                    $args = isset($call[1]) ? $this->resolveServices($call[1]) : array();
                }

                $definition->addMethodCall($method, $args);
            }
        }

        if (isset($service['tags'])) {
            if (!is_array($service['tags'])) {
                $message = sprintf(
                    'Parameter "tags" must be an array for service "%s" in %s. Check your JSON syntax',
                    $id,
                    $file
                );
                throw new InvalidArgumentException($message);
            }

            foreach ($service['tags'] as $tag) {
                if (!is_array($tag)) {
                    $message = sprintf(
                        'A "tags" entry must be an array for service "%s" in %s. Check your JSON syntax',
                        $id,
                        $file
                    );
                    throw new InvalidArgumentException($message);
                }

                if (!isset($tag['name'])) {
                    $message = sprintf('A "tags" entry is missing a "name" key for service "%s" in %s', $id, $file);
                    throw new InvalidArgumentException($message);
                }

                if (!is_string($tag['name']) || '' === $tag['name']) {
                    $message = sprintf('The tag name for service "%s" in %s must be a non-empty string', $id, $file);
                    throw new InvalidArgumentException($message);
                }

                $name = $tag['name'];
                unset($tag['name']);

                foreach ($tag as $attribute => $value) {
                    if (!is_scalar($value) && null !== $value) {
                        $message = sprintf(
                            'A "tags" attribute must be of a scalar-type for service "%s", tag "%s", '
                                .'attribute "%s" in %s. Check your JSON syntax',
                            $id,
                            $name,
                            $attribute,
                            $file
                        );
                        throw new InvalidArgumentException($message);
                    }
                }

                $definition->addTag($name, $tag);
            }
        }

        if (isset($service['decorates'])) {
            if ('' !== $service['decorates'] && '@' === $service['decorates'][0]) {
                $message = sprintf(
                    'The value of the "decorates" option for the "%s" service must be the id of the service without '
                        .'the "@" prefix (replace "%s" with "%s")',
                    $id,
                    $service['decorates'],
                    substr($service['decorates'], 1)
                );
                throw new InvalidArgumentException($message);
            }

            $renameId = isset($service['decoration_inner_name']) ? $service['decoration_inner_name'] : null;
            $priority = isset($service['decoration_priority']) ? $service['decoration_priority'] : 0;
            $definition->setDecoratedService($service['decorates'], $renameId, $priority);
        }

        if (isset($service['autowire'])) {
            $definition->setAutowired($service['autowire']);
        }

        if (isset($service['autowiring_types'])) {
            if (is_string($service['autowiring_types'])) {
                $definition->addAutowiringType($service['autowiring_types']);
            } else {
                if (!is_array($service['autowiring_types'])) {
                    $message = sprintf(
                        'Parameter "autowiring_types" must be a string or an array for service "%s" in %s. '
                            .'Check your JSON syntax',
                        $id,
                        $file
                    );
                    throw new InvalidArgumentException($message);
                }

                foreach ($service['autowiring_types'] as $autowiringType) {
                    if (!is_string($autowiringType)) {
                        $message = sprintf(
                            'A "autowiring_types" attribute must be of type string for service "%s" in %s. '
                                .'Check your JSON syntax',
                            $id,
                            $file
                        );
                        throw new InvalidArgumentException($message);
                    }

                    $definition->addAutowiringType($autowiringType);
                }
            }
        }

        $this->container->setDefinition($id, $definition);
    }

    /**
     * Parses a callable
     *
     * @param string|array $callable  A callable
     * @param string       $parameter A parameter (e.g. 'factory' or 'configurator')
     * @param string       $id        A service identifier
     * @param string       $file      A parsed file
     *
     * @throws InvalidArgumentException When errors occurred
     *
     * @return string|array A parsed callable
     */
    private function parseCallable($callable, $parameter, $id, $file)
    {
        if (is_string($callable)) {
            if ('' !== $callable && '@' === $callable[0]) {
                $message = sprintf(
                    'The value of the "%s" option for the "%s" service must be the id of the service without the "@" '
                        .'prefix (replace "%s" with "%s").',
                    $parameter,
                    $id,
                    $callable,
                    substr($callable, 1)
                );
                throw new InvalidArgumentException($message);
            }

            if (false !== strpos($callable, ':') && false === strpos($callable, '::')) {
                $parts = explode(':', $callable);

                return [$this->resolveServices('@'.$parts[0]), $parts[1]];
            }

            return $callable;
        }

        if (is_array($callable)) {
            if (isset($callable[0]) && isset($callable[1])) {
                return [$this->resolveServices($callable[0]), $callable[1]];
            }

            $message = sprintf(
                'Parameter "%s" must contain an array with two elements for service "%s" in %s. Check your JSON syntax',
                $parameter,
                $id,
                $file
            );
            throw new InvalidArgumentException($message);
        }

        $message = sprintf(
            'Parameter "%s" must be a string or an array for service "%s" in %s. Check your JSON syntax',
            $parameter,
            $id,
            $file
        );
        throw new InvalidArgumentException($message);
    }

    /**
     * Loads a JSON file
     *
     * @param string $file The file path
     *
     * @return array
     *
     * @throws InvalidArgumentException When the given file is not local
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

        $configuration = json_decode(file_get_contents($file), true);

        return $this->validate($configuration, $file);
    }

    /**
     * Validates a JSON file
     *
     * @param mixed  $content The content
     * @param string $file    The file path
     *
     * @return array
     *
     * @throws InvalidArgumentException When service file is not valid
     */
    private function validate($content, $file)
    {
        if ($content === null) {
            return $content;
        }

        if (!is_array($content)) {
            $message = sprintf(
                'The service file "%s" is not valid. It should contain an array. Check your JSON syntax',
                $file
            );
            throw new InvalidArgumentException($message);
        }

        foreach ($content as $namespace => $data) {
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

        return $content;
    }

    /**
     * Resolves services
     *
     * @param string|array $value
     *
     * @return array|string|Reference
     */
    private function resolveServices($value)
    {
        if (is_array($value)) {
            $value = array_map([$this, 'resolveServices'], $value);
        } elseif (is_string($value) &&  0 === strpos($value, '@=')) {
            return new Expression(substr($value, 2));
        } elseif (is_string($value) &&  0 === strpos($value, '@')) {
            if (0 === strpos($value, '@@')) {
                $value = substr($value, 1);
                $invalidBehavior = null;
            } elseif (0 === strpos($value, '@?')) {
                $value = substr($value, 2);
                $invalidBehavior = ContainerInterface::IGNORE_ON_INVALID_REFERENCE;
            } else {
                $value = substr($value, 1);
                $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
            }

            if ('=' === substr($value, -1)) {
                $value = substr($value, 0, -1);
                $strict = false;
            } else {
                $strict = true;
            }

            if (null !== $invalidBehavior) {
                $value = new Reference($value, $invalidBehavior, $strict);
            }
        }

        return $value;
    }

    /**
     * Loads from Extensions
     *
     * @param array $content
     *
     * @return void
     */
    private function loadFromExtensions($content)
    {
        foreach ($content as $namespace => $values) {
            if (in_array($namespace, ['imports', 'parameters', 'services'])) {
                continue;
            }

            if (!is_array($values)) {
                $values = [];
            }

            $this->container->loadFromExtension($namespace, $values);
        }
    }

    /**
     * Checks the keywords used to define a service
     *
     * @param string $id         The service name
     * @param array  $definition The service definition to check
     * @param string $file       The loaded YAML file
     *
     * @return void
     */
    private static function checkDefinition($id, array $definition, $file)
    {
        foreach ($definition as $key => $value) {
            if (!isset(static::$keywords[$key])) {
                $message = sprintf(
                    'The configuration key "%s" is unsupported for service definition "%s" in "%s". '
                        .'Allowed configuration keys are "%s"',
                    $key,
                    $id,
                    $file,
                    implode('", "', static::$keywords)
                );
                throw new InvalidArgumentException($message);
            }
        }
    }
}
