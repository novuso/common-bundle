<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\DataType\Doctrine\DBAL\Identifier;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Exception;
use Novuso\Common\Domain\Model\Identifier\Uuid;

/**
 * UuidType is the database type for a UUID
 *
 * @copyright Copyright (c) 2016, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class UuidType extends Type
{
    /**
     * Type name
     *
     * @var string
     */
    const TYPE_NAME = 'common_uuid';

    /**
     * Gets the SQL declaration snippet for a field of this type
     *
     * @param array            $fieldDeclaration The field declaration
     * @param AbstractPlatform $platform         The currently used database platform
     *
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * Converts a value from its PHP representation to its database representation
     *
     * @param mixed            $value    The value to convert
     * @param AbstractPlatform $platform The currently used database platform
     *
     * @return mixed
     *
     * @throws ConversionException When the conversion fails
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        if (!($value instanceof Uuid)) {
            throw ConversionException::conversionFailed($value, static::TYPE_NAME);
        }

        return $value->toString();
    }

    /**
     * Converts a value from its database representation to its PHP representation
     *
     * @param mixed            $value    The value to convert
     * @param AbstractPlatform $platform The currently used database platform
     *
     * @return mixed
     *
     * @throws ConversionException When the conversion fails
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof Uuid) {
            return $value;
        }

        try {
            $uuid = Uuid::parse($value);
        } catch (Exception $exception) {
            throw ConversionException::conversionFailed($value, static::TYPE_NAME);
        }

        return $uuid;
    }

    /**
     * Gets the name of this type
     *
     * @return string
     */
    public function getName()
    {
        return static::TYPE_NAME;
    }

    /**
     * Checks if this type requires a SQL comment hint
     *
     * @param AbstractPlatform $platform The currently used database platform
     *
     * @return boolean
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
