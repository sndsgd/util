<?php

namespace sndsgd;

use PHPUnit\Framework\TestCase;
use LogicException;

class VariableUtilTest extends TestCase
{
    /**
     * @dataProvider provideExport
     */
    public function testExport(
        mixed $value,
        int $arrayNestingDepth,
        mixed $expect,
    ): void {
        $this->assertSame(
            $expect,
            VariableUtil::export($value, $arrayNestingDepth),
        );
    }

    public function provideExport(): iterable
    {
        yield [null, 0, "null"];
        yield [42, 0, "42"];
        yield [false, 0, "false"];
        yield [true, 0, "true"];
        yield ["hello", 0, "'hello'"];
        yield [[123], 0, "[\n    123,\n]"];
        yield [
            [
                "foo",
                [
                    null,
                    true,
                    false,
                    123,
                    123.0,
                    "testing...",
                    [
                        "argh" => "ugh",
                        "hrmph" => [],
                    ],
                ],
            ],
            0,
            <<<STR
            [
                'foo',
                [
                    null,
                    true,
                    false,
                    123,
                    123.0,
                    'testing...',
                    [
                        'argh' => 'ugh',
                        'hrmph' => [],
                    ],
                ],
            ]
            STR,
        ];
    }

    public function testExportUnsupportedTypeException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("support for object values is not implemented");
        VariableUtil::export(new \stdClass());
    }
}
