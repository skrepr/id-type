<?php

declare(strict_types=1);

namespace Skrepr\IdType\Persistence\Doctrine;

use Skrepr\IdType\ValueObject\AbstractUuid;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Uid\Uuid;

abstract class AbstractUuidType extends Type
{
    abstract protected function getClassName(): string;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return (new GuidType())->getSQLDeclaration($column, $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?AbstractUuid
    {
        $uuidValue = (new GuidType())->convertToPHPValue($value, $platform);
        if ($uuidValue === null) {
            return null;
        }

        $className = '\\' . $this->getClassName();

        $idObject = new $className($uuidValue);
        if (!$idObject instanceof AbstractUuid) {
            throw new \TypeError($className . ' is not of type ' . AbstractUuid::class);
        }

        return $idObject;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?Uuid
    {
        if ($value instanceof AbstractUuid) {
            return (new GuidType())->convertToDatabaseValue($value->getId(), $platform);
        }

        return null;
    }
}
