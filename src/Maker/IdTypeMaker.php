<?php

declare(strict_types=1);

namespace Skrepr\IdType\Maker;

use InvalidArgumentException;
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
use Symfony\Component\Console\Input\InputOption;
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
            ->addOption('namespace', 's', InputOption::VALUE_REQUIRED, 'Extra namespace (single word) for the new UuidType (e.g. <fg=yellow>Shop</>)')
            ->addOption('register', 'r', InputOption::VALUE_NONE, 'Register Id Type to config/doctrine.yaml')
        ;
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $extraNamespace = '';
        $extraName = '';
        if ($input->hasOption('namespace')) {
            $namespace = $input->getOption('namespace');
            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/i', $namespace)) {
                throw new InvalidArgumentException(sprintf('The namespace "%s" is not a valid namespace.', $namespace));
            }

            $extraNamespace = ucfirst($namespace) . '\\';
            $extraName = strtolower($namespace) . '_';
        }

        $persistenceClassNameDetails = $generator->createClassNameDetails(
            $input->getArgument('name') . 'Type',
            'Persistence\\Doctrine\\' . $extraNamespace,
        );
        $idTypeClassNameDetails = $generator->createClassNameDetails(
            $input->getArgument('name'),
            'ValueObject\\' . $extraNamespace
        );

        $typeName = $extraName . $input->getArgument('name');
        $generator->generateClass(
            $idTypeClassNameDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/Id.tpl.php',
            [
                'type_id_name' => $typeName,
            ]
        );

        $generator->generateClass(
            $persistenceClassNameDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/IdType.tpl.php',
            [
                'type_id_class_name_full' => $idTypeClassNameDetails->getFullName(),
                'type_id_class_name' => $idTypeClassNameDetails->getShortName(),
                'type_id_name' => $typeName,
            ]
        );

        if ($input->getOption('register') !== false) {
            $manipulator = new YamlSourceManipulator($this->fileManager->getFileContents('config/packages/doctrine.yaml'));
            $doctrineData = $manipulator->getData();
            $doctrineData['doctrine']['dbal']['types'][$typeName] = $persistenceClassNameDetails->getFullName();
            $manipulator->setData($doctrineData);

            $generator->dumpFile('config/packages/doctrine.yaml', $manipulator->getContents());
        }
        $generator->writeChanges();

        $this->writeSuccessMessage($io);
    }
}
