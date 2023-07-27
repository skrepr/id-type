<?php

declare(strict_types=1);

namespace Skrepr\IdType\ValueObject;

use Skrepr\IdType\Exception\UnableToGenerateException;

abstract class AbstractIntegerId extends AbstractId
{
    protected int $id;

    final public function __construct(int|string $id)
    {
        $this->id = (int) $id;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
