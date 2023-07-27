<?php

declare(strict_types=1);

namespace Skrepr\Tests\ValueResolver;

use Skrepr\Example\ValueObject\UserId;
use Skrepr\IdType\ValueResolver\IdValueResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IdValueResolverTest extends TestCase
{
    public function testWithValidUuid(): void
    {
        $request = new Request(attributes: ['user' => '427ca064-156c-4e7e-82f2-b9d55db17e8e']);
        $argument = new ArgumentMetadata(
            'user',
            UserId::class,
            false,
            false,
            null
        );
        $resolver = new IdValueResolver();
        $result = $resolver->resolve($request, $argument);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(UserId::class, $result[0]);
    }

    public function testWithNullableValue(): void
    {
        $request = new Request();
        $argument = new ArgumentMetadata(
            'user',
            UserId::class,
            false,
            false,
            null,
            true,
        );
        $resolver = new IdValueResolver();
        $result = $resolver->resolve($request, $argument);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertNull($result[0]);
    }

    public function testWithInvalidUuid(): void
    {
        $request = new Request(attributes: ['user' => 'invalid']);
        $argument = new ArgumentMetadata(
            'user',
            UserId::class,
            false,
            false,
            null
        );
        $resolver = new IdValueResolver();

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('The uid for the "user" parameter is invalid.');

        $resolver->resolve($request, $argument);
    }

    public function testWithEmptyValue(): void
    {
        $request = new Request();
        $argument = new ArgumentMetadata(
            'user',
            UserId::class,
            false,
            false,
            null,
        );
        $resolver = new IdValueResolver();

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('The uid for the "user" parameter can not be NULL.');

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
        $resolver = new IdValueResolver();

        $result = $resolver->resolve($request, $argument);
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }
}
