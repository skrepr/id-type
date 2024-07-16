<?php

declare(strict_types=1);

namespace App\Persistence\Doctrine;

use App\ValueObject\UserId;
use Skrepr\IdType\Persistence\Doctrine\AbstractUuidType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('skrepr.id-type')]
class UserIdType extends AbstractUuidType
{
    protected function getClassName(): string
    {
        return UserId::class;
    }
}