<a href="https://skrepr.com/">
  <p align="center">
    <img width="200" height="100" src="https://cdn.skrepr.com/logo/skrepr_liggend.svg" alt="skrepr_logo" />
  </p>
</a>
<h1 align="center">Skrepr Teams Connector Bundle</h1>
<div align="center">
  <a href="https://github.com/skrepr/id-type/releases"><img src="https://img.shields.io/github/release/skrepr/id-type.svg" alt="Releases"/></a><a> </a>
  <a href="https://github.com/skrepr/id-type/blob/main/LICENSE"><img src="https://img.shields.io/github/license/skrepr/id-type.svg" alt="LICENSE"/></a><a> </a>
  <a href="https://github.com/skrepr/id-type/issues"><img src="https://img.shields.io/github/issues/skrepr/id-type.svg" alt="Issues"/></a><a> </a>
  <a href="https://github.com/skrepr/id-type/stars"><img src="https://img.shields.io/github/stars/skrepr/id-type.svg" alt="Stars"/></a><a> </a>
</div>

Symfony bundle for generating and validating ID types

## Prerequisites

This version of the project requires:
* PHP 8.2+
* Symfony 6.0+

## Installation

You can install the library through composer:

``` bash
$ composer require skrepr/id-type
```

The bundle should be enabled by syfmony/flex but if not:

``` php
// config/bundles.php

<?php

return [
    Skrepr\IdType\SkreprIdTypeBundle::class => ['all' => true],
];

```

## Usage
To generate an UuidType:

```bash
bin/console make:uuid-type <id_name>
```

Where `id_name` is something like "user_id".

With this maker command, two files are generated (`src/ValueObject/UserId.php` and `src/Persistence/Doctrine/UserIdType.php`) 
and also the new type is added to `config/packages/doctrine.yaml`.

To use this new id in your entity:

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use App\ValueObject\UserId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: 'user_id')]
    public readonly UserId $id;
    
    #[ORM\Column(type: 'string')]
    public string $name;
    
    public function __construct(string $name)
    {
        $this->id = UserId::generate();
        $this->name = $name;
    }
}
```

```php
// ...
UserId::generate();


// ...
``` 