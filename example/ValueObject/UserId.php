<?php

declare(strict_types=1);

namespace App\ValueObject;

use Skrepr\IdType\ValueObject\AbstractUuid;

class UserId extends AbstractUuid
{
    public const string TYPE = 'user_id';
}