<?php

namespace Novuso\Common\Adapter\Service\Symfony;

use Exception;
use Novuso\Common\Application\Service\Container;
use Novuso\Common\Application\Service\Exception\ServiceContainerException;
use Novuso\Common\Application\Service\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * SymfonyContainer is a Symfony service container adapter
 *
 * @copyright Copyright (c) 2016, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class SymfonyContainer implements Container
{
    /**
     * Service container
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructs SymfonyContainer
     *
     * @param ContainerInterface $container The service container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name)
    {
        if (!$this->container->has($name)) {
            throw ServiceNotFoundException::fromName($name);
        }
        try {
            return $this->container->get($name);
        } catch (Exception $exception) {
            throw new ServiceContainerException($exception->getMessage(), $name, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return $this->container->has($name);
    }
}
