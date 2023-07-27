<?php

declare(strict_types=1);

namespace Skrepr\IdType\ValueObject;

use Symfony\Component\Uid\Uuid;

abstract class AbstractUuid extends AbstractId
{
    protected Uuid $id;

    final public function __construct(Uuid|string $id)
    {
        if (!$id instanceof Uuid) {
            $id = Uuid::fromRfc4122($id);
        }
        $this->id = $id;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public static function generate(): static
    {
        return new static(Uuid::v4());
    }

    public function __toString(): string
    {
        return $this->getId()->toRfc4122();
    }
}
