<?php

declare(strict_types=1);

namespace Tests\Unit\OnceUponATime\Domain\Entity;

use OnceUponATime\Domain\Entity\Clue;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClueTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed_from_a_string_and_reverted_back_to_it()
    {
        $text = 'clue number 1';
        $clue = Clue::fromString($text);
        $this->assertSame($text, (string) $clue);
    }

    /**
     * @test
     */
    public function it_should_be_a_non_empty_string()
    {
        $this->expectException(\InvalidArgumentException::class);
        Clue::fromString('');
    }
}
