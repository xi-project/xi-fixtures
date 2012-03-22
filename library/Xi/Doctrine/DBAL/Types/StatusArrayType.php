<?php

namespace Xi\Doctrine\DBAL\Types;

use Doctrine\DBAL\Types\Type;


/**
 * Type that maps a PHP array to a clob SQL type.
 *
 * @since 2.0
 */
class StatusArrayType extends Type
{
    public function getSQLDeclaration(array $fieldDeclaration, \Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
        return $platform->getClobTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToDatabaseValue($value, \Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
        return serialize($value);
    }

    public function convertToPHPValue($value, \Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        $value = (is_resource($value)) ? stream_get_contents($value) : $value;
        $val = unserialize($value);
        if ($val === false && $value != 'b:0;') {
            throw ConversionException::conversionFailed($value, $this->getName());
        }
        return $val;
    }

    public function getName()
    {
        return Type::TARRAY;
    }
}