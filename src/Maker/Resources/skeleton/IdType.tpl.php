<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use <?= $type_id_class_name_full ?>;
use Skrepr\IdType\Persistence\Doctrine\AbstractUuidType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('skrepr.id-type')]
class <?= $class_name ?> extends AbstractUuidType
{
    protected function getClassName(): string
    {
        return <?= $type_id_class_name ?>::class;
    }
}
