<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use Skrepr\IdType\ValueObject\AbstractUuid;

class <?= $class_name ?> extends AbstractUuid
{
    public const string TYPE = '<?= $type_id_name ?>';
}
