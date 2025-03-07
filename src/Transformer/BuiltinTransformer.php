<?php

declare(strict_types=1);

namespace AutoMapper\Transformer;

use AutoMapper\Extractor\PropertyMapping;
use AutoMapper\Generator\UniqueVariableScope;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Name;
use Symfony\Component\PropertyInfo\Type;

/**
 * Built in transformer to handle PHP scalar types.
 *
 * @author Joel Wurtz <jwurtz@jolicode.com>
 */
final class BuiltinTransformer implements TransformerInterface
{
    private const CAST_MAPPING = [
        Type::BUILTIN_TYPE_BOOL => [
            Type::BUILTIN_TYPE_INT => Cast\Int_::class,
            Type::BUILTIN_TYPE_STRING => Cast\String_::class,
            Type::BUILTIN_TYPE_FLOAT => Cast\Double::class,
            Type::BUILTIN_TYPE_ARRAY => 'toArray',
            Type::BUILTIN_TYPE_ITERABLE => 'toArray',
        ],
        Type::BUILTIN_TYPE_FLOAT => [
            Type::BUILTIN_TYPE_STRING => Cast\String_::class,
            Type::BUILTIN_TYPE_INT => Cast\Int_::class,
            Type::BUILTIN_TYPE_BOOL => Cast\Bool_::class,
            Type::BUILTIN_TYPE_ARRAY => 'toArray',
            Type::BUILTIN_TYPE_ITERABLE => 'toArray',
        ],
        Type::BUILTIN_TYPE_INT => [
            Type::BUILTIN_TYPE_FLOAT => Cast\Double::class,
            Type::BUILTIN_TYPE_STRING => Cast\String_::class,
            Type::BUILTIN_TYPE_BOOL => Cast\Bool_::class,
            Type::BUILTIN_TYPE_ARRAY => 'toArray',
            Type::BUILTIN_TYPE_ITERABLE => 'toArray',
        ],
        Type::BUILTIN_TYPE_ITERABLE => [
            Type::BUILTIN_TYPE_ARRAY => 'fromIteratorToArray',
        ],
        Type::BUILTIN_TYPE_ARRAY => [],
        Type::BUILTIN_TYPE_STRING => [
            Type::BUILTIN_TYPE_ARRAY => 'toArray',
            Type::BUILTIN_TYPE_ITERABLE => 'toArray',
            Type::BUILTIN_TYPE_FLOAT => Cast\Double::class,
            Type::BUILTIN_TYPE_INT => Cast\Int_::class,
            Type::BUILTIN_TYPE_BOOL => Cast\Bool_::class,
        ],
        Type::BUILTIN_TYPE_CALLABLE => [],
        Type::BUILTIN_TYPE_RESOURCE => [],
    ];

    public function __construct(
        private Type $sourceType,
        /** @var Type[] $targetTypes */
        private array $targetTypes,
    ) {
    }

    public function transform(Expr $input, Expr $target, PropertyMapping $propertyMapping, UniqueVariableScope $uniqueVariableScope): array
    {
        $targetTypes = array_map(function (Type $type) {
            return $type->getBuiltinType();
        }, $this->targetTypes);

        // Source type is in target => no cast
        if (\in_array($this->sourceType->getBuiltinType(), $targetTypes, true)) {
            return [$input, []];
        }

        // Cast needed
        foreach (self::CAST_MAPPING[$this->sourceType->getBuiltinType()] as $castType => $castMethod) {
            if (\in_array($castType, $targetTypes, true)) {
                if (method_exists($this, $castMethod)) {
                    return [$this->$castMethod($input), []];
                }

                return [new $castMethod($input), []];
            }
        }

        return [$input, []];
    }

    private function toArray(Expr $input): Expr
    {
        return new Expr\Array_([new Expr\ArrayItem($input)]);
    }

    private function fromIteratorToArray(Expr $input): Expr
    {
        return new Expr\FuncCall(new Name('iterator_to_array'), [
            new Arg($input),
        ]);
    }
}
