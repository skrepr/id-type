<?php

declare(strict_types=1);

namespace Skrepr\IdType\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Util\YamlSourceManipulator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class IdTypeMaker extends AbstractMaker
{
    public function __construct(
        #[Autowire(service: 'maker.file_manager')] private FileManager $fileManager,
    ) {
    }

    public static function getCommandName(): string
    {
        return 'make:id-type';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a UuidType for Doctrine';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the new UuidType (e.g. <fg=yellow>user_id</>)')
        ;
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $persistenceClassNameDetails = $generator->createClassNameDetails(
            $input->getArgument('name') . 'Type',
            'Persistence\\Doctrine\\'
        );
        $idTypeClassNameDetails = $generator->createClassNameDetails(
            $input->getArgument('name'),
            'ValueObject\\'
        );

        $generator->generateClass(
            $idTypeClassNameDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/Id.tpl.php',
            [
            ]
        );

        $generator->generateClass(
            $persistenceClassNameDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/IdType.tpl.php',
            [
                'type_id_class_name_full' => $idTypeClassNameDetails->getFullName(),
                'type_id_class_name' => $idTypeClassNameDetails->getShortName(),
                'type_id_name' => $input->getArgument('name'),
            ]
        );

        $manipulator = new YamlSourceManipulator($this->fileManager->getFileContents('config/packages/doctrine.yaml'));
        $doctrineData = $manipulator->getData();
        $doctrineData['doctrine']['dbal']['types'][$input->getArgument('name')] = $persistenceClassNameDetails->getFullName();
        $manipulator->setData($doctrineData);

        $generator->dumpFile('config/packages/doctrine.yaml', $manipulator->getContents());

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
    }
}
