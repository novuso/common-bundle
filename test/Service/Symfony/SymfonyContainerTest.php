<?php

namespace Novuso\Test\Common\Adapter\Service\Symfony;

use Novuso\Common\Adapter\Service\Symfony\SymfonyContainer;
use Novuso\Test\System\TestCase\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers Novuso\Common\Adapter\Service\Symfony\SymfonyContainer
 */
class SymfonyContainerTest extends UnitTestCase
{
    public function test_that_get_returns_expected_instance()
    {
        $symfonyContainer = new ContainerBuilder();
        $symfonyContainer->setDefinition('test.date_time', new Definition('DateTime', ['2016-01-01']));
        $container = new SymfonyContainer($symfonyContainer);
        $dateTime = $container->get('test.date_time');
        $this->assertSame('2016-01-01', $dateTime->format('Y-m-d'));
    }

    public function test_that_has_returns_true_when_service_is_defined()
    {
        $symfonyContainer = new ContainerBuilder();
        $symfonyContainer->setDefinition('test.date_time', new Definition('DateTime', ['2016-01-01']));
        $container = new SymfonyContainer($symfonyContainer);
        $this->assertTrue($container->has('test.date_time'));
    }

    public function test_that_has_returns_false_when_service_is_not_defined()
    {
        $symfonyContainer = new ContainerBuilder();
        $symfonyContainer->setDefinition('test.date_time', new Definition('DateTime', ['2016-01-01']));
        $container = new SymfonyContainer($symfonyContainer);
        $this->assertFalse($container->has('date_time'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Service\Exception\ServiceNotFoundException
     */
    public function test_that_get_throws_exception_when_service_is_not_defined()
    {
        $symfonyContainer = new ContainerBuilder();
        $symfonyContainer->setDefinition('test.date_time', new Definition('DateTime', ['2016-01-01']));
        $container = new SymfonyContainer($symfonyContainer);
        $container->get('date_time');
    }

    /**
     * @expectedException \Novuso\Common\Application\Service\Exception\ServiceContainerException
     */
    public function test_that_get_throws_exception_on_error()
    {
        $symfonyContainer = new ContainerBuilder();
        $symfonyContainer->setDefinition('test.date_time', new Definition('FooBarBaz'));
        $container = new SymfonyContainer($symfonyContainer);
        $container->get('test.date_time');
    }
}
