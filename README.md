<a href="https://skrepr.com/">
  <p align="center">
    <img width="200" height="100" src="https://cdn.skrepr.com/logo/skrepr_liggend.svg" alt="skrepr_logo" />
  </p>
</a>
<h1 align="center">Skrepr ID Types</h1>
<div align="center">
  <a href="https://github.com/skrepr/id-type/releases"><img src="https://img.shields.io/github/release/skrepr/id-type.svg" alt="Releases"/></a><a> </a>
  <a href="https://github.com/skrepr/id-type/blob/main/LICENSE"><img src="https://img.shields.io/github/license/skrepr/id-type.svg" alt="LICENSE"/></a><a> </a>
  <a href="https://github.com/skrepr/id-type/issues"><img src="https://img.shields.io/github/issues/skrepr/id-type.svg" alt="Issues"/></a><a> </a>
  <a href="https://github.com/skrepr/id-type/stars"><img src="https://img.shields.io/github/stars/skrepr/id-type.svg" alt="Stars"/></a><a> </a>
</div>

Symfony bundle for generating and validating ID types

## Prerequisites

This version of the project requires:
* PHP 8.3+
* Symfony 6.4+

## Installation

You can install the library through composer:

``` bash
composer require skrepr/id-type
```

The bundle should be enabled by symfony/flex, but if not:

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
bin/console make:id-type [--register] <id_name>
```

Where `id_name` is something like "user_id".

With this maker command, two files are generated (`src/ValueObject/UserId.php` and `src/Persistence/Doctrine/UserIdType.php`) 
and if the `--register` option is given, also the new type is added to `config/packages/doctrine.yaml`.

Registering is not needed if you are using autoconfigure because of the service tag "skrepr.id-type" will automatically
register the type to doctrine.

To use this new id in your entity (example:
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
```
See the example directory for the generated files by make:id-type.

To generate a new ID you can use the static `generate`-function:
```php
$newId = UserId::generate();
```

You can also feed a UUID or string to the constructor:
```php
$userId = new UserId('00000000-0000-0000-0000-000000000000');
// or
$userId = new UserId( \Symfony\Component\Uid\Uuid::v4() );
```


### AutoConfigure
By default, the created ID's can be used with AutoConfigure from Symfony. To upgrade existing ID's to this system you have to
do the following (in this example we use "UserId").

1. Remove the 'user_id'-line from config/packages/doctrine.yaml
    ```yaml
    doctrine:
        dbal:
            types:
                user_id: App\Persistence\Doctrine\UserIdType
    ```
2. Add the service tag 'skrepr.id-type' to App\Persistence\Doctrine\UserIdType
    ```php
    #[AutoconfigureTag('skrepr.id-type')]
    class UserIdType extends AbstractUuidType 
    ```
3. (optional) Remove the function App\Persistence\Doctrine\UserIdType::getName  
4. (optional) Add a constant to App\ValueObject\TestId (required if step 3 is done)
    ```php
    public const string TYPE = 'user_id';
    ```
   
Instead of step 2, you can also add the tag to all your custom types at once, because the compiler pass of skrepr/id-type will check for a subclass of AbstractUuidType:
```yaml
# config/service.yaml
    App\Persistence\Doctrine\:
        resource: '../src/Persistence/Doctrine/'
        tags:
            - { name: 'skrepr.id-type' }
```
