<?php

declare(strict_types=1);

namespace Tests\Unit\OnceUponATime\Domain\Entity\Event;

use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\User\ExternalUserId;
use OnceUponATime\Domain\Entity\User\Name;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Domain\Event\QuizzEvent;
use OnceUponATime\Domain\Event\QuizzEventStore;
use OnceUponATime\Domain\Event\UserRegistered;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRegisteredTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_constructed_with_a_question_id_and_a_user_id()
    {
        $userId = UserId::fromString('3a021c08-ad15-43aa-aba3-8626fecd39a7');
        $externalUserId = ExternalUserId::fromString('external_user_id');
        $name = Name::fromString('Samir');
        $userRegistered = new UserRegistered($userId, $externalUserId, $name);
        $this->assertInstanceOf(QuizzEvent::class, $userRegistered);
        $this->assertSame($userId, $userRegistered->userId());
        $this->assertSame($externalUserId, $userRegistered->externalUserId());
        $this->assertSame($name, $userRegistered->name());
    }
}
