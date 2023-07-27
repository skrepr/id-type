<?php

declare(strict_types=1);

namespace Skrepr\Tests\ValueResolver;

use Skrepr\Example\Entity\User;
use Skrepr\IdType\ValueResolver\EntityValueResolver;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityValueResolverTest extends TestCase
{
    // used to make it easier to test incorrect entities (use this object as "entity")
    private ?string $testProperty = null;

    public function testWithValidUuid(): void
    {
        $classMetaData = $this->createMock(ClassMetadata::class);
        $classMetaData->method('getSingleIdReflectionProperty')->willReturn(
            new ReflectionProperty(User::class, 'id')
        );

        $manager = $this->createMock(ObjectManager::class);
        $manager->method('getClassMetadata')->willReturn($classMetaData);
        $manager->method('find')->willReturn(new User('naam', 'email', ['roles']));

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($manager);

        $request = new Request(attributes: ['user' => '427ca064-156c-4e7e-82f2-b9d55db17e8e']);
        $argument = new ArgumentMetadata(
            'user',
            User::class,
            false,
            false,
            null
        );
        $resolver = new EntityValueResolver($registry);
        $result = $resolver->resolve($request, $argument);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(User::class, $result[0]);
    }

    public function testWithInvalidUuid(): void
    {
        $classMetaData = $this->createMock(ClassMetadata::class);
        $classMetaData->method('getSingleIdReflectionProperty')->willReturn(
            new ReflectionProperty(User::class, 'id')
        );

        $manager = $this->createMock(ObjectManager::class);
        $manager->method('getClassMetadata')->willReturn($classMetaData);
        $manager->method('find')->willReturn(null);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($manager);

        $request = new Request(attributes: ['user' => 'invalid']);
        $argument = new ArgumentMetadata(
            'user',
            User::class,
            false,
            false,
            null
        );
        $resolver = new EntityValueResolver($registry);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('The uid for the "user" parameter is invalid.');

        $resolver->resolve($request, $argument);
    }

    public function testWithInvalidParameter(): void
    {
        $classMetaData = $this->createMock(ClassMetadata::class);
        $classMetaData->method('getSingleIdReflectionProperty')->willReturn(
            new ReflectionProperty(User::class, 'id')
        );

        $manager = $this->createMock(ObjectManager::class);
        $manager->method('getClassMetadata')->willReturn($classMetaData);
        $manager->method('find')->willReturn(null);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($manager);

        $request = new Request(attributes: ['user' => 0]);
        $argument = new ArgumentMetadata(
            'user',
            User::class,
            false,
            false,
            null
        );
        $resolver = new EntityValueResolver($registry);

        $result = $resolver->resolve($request, $argument);
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testWithUnknownUuid(): void
    {
        $classMetaData = $this->createMock(ClassMetadata::class);
        $classMetaData->method('getSingleIdReflectionProperty')->willReturn(
            new ReflectionProperty(User::class, 'id')
        );

        $manager = $this->createMock(ObjectManager::class);
        $manager->method('getClassMetadata')->willReturn($classMetaData);
        $manager->method('find')->willReturn(null);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($manager);

        $request = new Request(attributes: ['user' => '427ca064-156c-4e7e-82f2-b9d55db17e8e']);
        $argument = new ArgumentMetadata(
            'user',
            User::class,
            false,
            false,
            null
        );
        $resolver = new EntityValueResolver($registry);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('"Skrepr\Example\Entity\User" object not found by "' . EntityValueResolver::class . '".');

        $resolver->resolve($request, $argument);
    }

    public function testOtherObject(): void
    {
        $request = new Request();
        $argument = new ArgumentMetadata(
            'user',
            self::class,
            false,
            false,
            null,
        );
        $resolver = new EntityValueResolver($this->createMock(ManagerRegistry::class));

        $result = $resolver->resolve($request, $argument);
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testWithScalarType(): void
    {
        $request = new Request();
        $argument = new ArgumentMetadata(
            'user',
            'string',
            false,
            false,
            null,
        );

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->willThrowException(new \ReflectionException());

        $resolver = new EntityValueResolver($managerRegistry);
        $result = $resolver->resolve($request, $argument);
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testWithNonUuidKey(): void
    {
        $classMetaData = $this->createMock(ClassMetadata::class);
        $classMetaData->method('getSingleIdReflectionProperty')->willReturn(
            new ReflectionProperty(self::class, 'testProperty')
        );

        $manager = $this->createMock(ObjectManager::class);
        $manager->method('getClassMetadata')->willReturn($classMetaData);
        $manager->method('find')->willReturn(null);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($manager);

        $request = new Request(attributes: ['user' => '']);
        $argument = new ArgumentMetadata(
            'user',
            User::class,
            false,
            false,
            null
        );
        $resolver = new EntityValueResolver($registry);

        $result = $resolver->resolve($request, $argument);
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }
}
