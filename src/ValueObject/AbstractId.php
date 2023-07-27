<?php

declare(strict_types=1);

namespace Skrepr\IdType\ValueObject;

use Skrepr\IdType\Exception\InvalidImplementationException;
use Skrepr\IdType\Exception\UnableToGenerateException;

abstract class AbstractId
{
    public function __construct()
    {
        throw new InvalidImplementationException('This class should not be used for normal operation');
    }

    public static function generate(): static
    {
        throw new UnableToGenerateException( __CLASS__ . '::generate is not implemented');
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }

    public static function fromString(string $id): static
    {
        return new static($id);
    }
}
