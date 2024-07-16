<?php

declare(strict_types=1);

namespace App\Entity;

use App\ValueObject\UserId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: UserId::TYPE)]
    public readonly UserId $id;

    #[ORM\Column(type: 'string')]
    public string $name;

    public function __construct(string $name)
    {
        $this->id = UserId::generate();
        $this->name = $name;
    }
}