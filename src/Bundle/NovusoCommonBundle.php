<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Bundle;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Novuso\Common\Adapter\Bundle\DependencyInjection\Compiler\CommandFilterCompilerPass;
use Novuso\Common\Adapter\Bundle\DependencyInjection\Compiler\CommandHandlerCompilerPass;
use Novuso\Common\Adapter\Bundle\DependencyInjection\Compiler\EventSubscriberCompilerPass;
use Novuso\Common\Adapter\Bundle\DependencyInjection\Compiler\QueryFilterCompilerPass;
use Novuso\Common\Adapter\Bundle\DependencyInjection\Compiler\QueryHandlerCompilerPass;
use Novuso\Common\Adapter\DataType\Doctrine\DBAL\Basic\MbStringType;
use Novuso\Common\Adapter\DataType\Doctrine\DBAL\Basic\CStringType;
use Novuso\Common\Adapter\DataType\Doctrine\DBAL\DateTime\DateTimeType;
use Novuso\Common\Adapter\DataType\Doctrine\DBAL\DateTime\DateType;
use Novuso\Common\Adapter\DataType\Doctrine\DBAL\DateTime\TimeType;
use Novuso\Common\Adapter\DataType\Doctrine\DBAL\DateTime\TimezoneType;
use Novuso\Common\Adapter\DataType\Doctrine\DBAL\Identifier\UuidType;
use Novuso\Common\Adapter\DataType\Doctrine\DBAL\Money\CurrencyType;
use Novuso\Common\Adapter\DataType\Doctrine\DBAL\Money\MoneyType;
use Novuso\Common\Adapter\DataType\Doctrine\DBAL\Resource\UriType;
use Novuso\Common\Adapter\DataType\Doctrine\DBAL\Resource\UrlType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * NovusoCommonBundle is the Symfony Bundle for Novuso Common
 *
 * @copyright Copyright (c) 2017, Novuso. <http://novuso.com>
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
            Type::addType(MbStringType::TYPE_NAME, MbStringType::class);
            Type::addType(CStringType::TYPE_NAME, CStringType::class);
            Type::addType(DateTimeType::TYPE_NAME, DateTimeType::class);
            Type::addType(DateType::TYPE_NAME, DateType::class);
            Type::addType(TimeType::TYPE_NAME, TimeType::class);
            Type::addType(TimezoneType::TYPE_NAME, TimezoneType::class);
            Type::addType(UuidType::TYPE_NAME, UuidType::class);
            Type::addType(CurrencyType::TYPE_NAME, CurrencyType::class);
            Type::addType(MoneyType::TYPE_NAME, MoneyType::class);
            Type::addType(UriType::TYPE_NAME, UriType::class);
            Type::addType(UrlType::TYPE_NAME, UrlType::class);
            /** @var EntityManager $entityManager */
            $entityManager = $this->container->get('doctrine')->getManager();
            /** @var AbstractPlatform $platform */
            $platform = $entityManager->getConnection()->getDatabasePlatform();
            $platform->registerDoctrineTypeMapping(
                'db_'.MbStringType::TYPE_NAME,
                MbStringType::TYPE_NAME
            );
            $platform->registerDoctrineTypeMapping(
                'db_'.CStringType::TYPE_NAME,
                CStringType::TYPE_NAME
            );
            $platform->registerDoctrineTypeMapping(
                'db_'.DateTimeType::TYPE_NAME,
                DateTimeType::TYPE_NAME
            );
            $platform->registerDoctrineTypeMapping(
                'db_'.DateType::TYPE_NAME,
                DateType::TYPE_NAME
            );
            $platform->registerDoctrineTypeMapping(
                'db_'.TimeType::TYPE_NAME,
                TimeType::TYPE_NAME
            );
            $platform->registerDoctrineTypeMapping(
                'db_'.TimezoneType::TYPE_NAME,
                TimezoneType::TYPE_NAME
            );
            $platform->registerDoctrineTypeMapping(
                'db_'.UuidType::TYPE_NAME,
                UuidType::TYPE_NAME
            );
            $platform->registerDoctrineTypeMapping(
                'db_'.CurrencyType::TYPE_NAME,
                CurrencyType::TYPE_NAME
            );
            $platform->registerDoctrineTypeMapping(
                'db_'.MoneyType::TYPE_NAME,
                MoneyType::TYPE_NAME
            );
            $platform->registerDoctrineTypeMapping(
                'db_'.UriType::TYPE_NAME,
                UriType::TYPE_NAME
            );
            $platform->registerDoctrineTypeMapping(
                'db_'.UrlType::TYPE_NAME,
                UrlType::TYPE_NAME
            );
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
