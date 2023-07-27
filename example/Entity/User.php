<?php

declare(strict_types=1);

namespace Skrepr\Example\Entity;

use Skrepr\Example\ValueObject\UserId;

class User
{
    public UserId $id;
    public string $name;
}
