<?php

declare(strict_types=1);

namespace Tests\Unit\OnceUponATime\Domain\Entity\User;

use OnceUponATime\Domain\Entity\User\Name;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NameTest extends TestCase
{
    /**
     * @test
     */
    public function it_tests_wraps_a_string()
    {
        $string = 'Alice';
        $name = Name::fromString($string);
        $this->assertSame($string, (string) $name);
    }

    /**
     * @test
     */
    public function it_should_be_a_non_empty_string()
    {
        $this->expectException(\InvalidArgumentException::class);
        Name::fromString('');
    }
}
