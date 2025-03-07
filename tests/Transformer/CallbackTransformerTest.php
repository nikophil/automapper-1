<?php

declare(strict_types=1);

namespace AutoMapper\Tests\Transformer;

use AutoMapper\Transformer\CallbackTransformer;
use PHPUnit\Framework\TestCase;

class CallbackTransformerTest extends TestCase
{
    use EvalTransformerTrait;

    public function testCallbackTransform()
    {
        $transformer = new CallbackTransformer('test');
        $function = $this->createTransformerFunction($transformer);
        $class = new class() {
            public $callbacks;

            public function __construct()
            {
                $this->callbacks['test'] = function ($input) {
                    return 'output';
                };
            }
        };

        $transform = \Closure::bind($function, $class);

        $output = $transform('input');

        self::assertEquals('output', $output);
    }
}
