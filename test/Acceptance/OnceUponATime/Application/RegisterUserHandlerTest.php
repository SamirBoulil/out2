<?php

declare(strict_types=1);

namespace Tests\Acceptance\OnceUponATime\Application;

use OnceUponATime\Application\RegisterUser\RegisterUser;
use OnceUponATime\Application\RegisterUser\RegisterUserHandler;
use OnceUponATime\Application\RegisterUser\UserRegisteredNotify;
use OnceUponATime\Domain\Entity\User\ExternalUserId;
use OnceUponATime\Domain\Repository\UserRepository;
use OnceUponATime\Infrastructure\Notifications\UserRegisteredNotifyMany;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterUserHandlerTest extends TestCase
{
    /** @var UserRegisteredNotify */
    private $testEventSubscriber;

    /** @var UserRepository */
    private $userRepository;

    /** @var RegisterUserHandler */
    private $registerUserHandler;

    public function setUp()
    {
        $this->testEventSubscriber = new TestEventSubscriber();
        $this->userRepository = new InMemoryUserRepository();
        $this->registerUserHandler = new RegisterUserHandler(
            $this->userRepository,
            new UserRegisteredNotifyMany([$this->testEventSubscriber])
        );
    }

    /**
     * @test
     */
    public function it_registers_a_new_user_with_valid_information_and_notifies()
    {
        $externalUserId = '<@valid_external_user_id>';
        $registerUser = new RegisterUser();
        $registerUser->externalUserId = $externalUserId;
        $registerUser->name = 'valid_user_name';
        $this->registerUserHandler->register($registerUser);
        $user = $this->userRepository->byExternalId(ExternalUserId::fromString($externalUserId));
        $this->assertNotNull($user);
        $this->assertTrue($this->testEventSubscriber->isUserRegistered);
    }

    /**
     * @test
     */
    public function it_does_not_register_a_new_user_with_empty_information()
    {
        $registerUser = new RegisterUser();
        $this->expectException(\Exception::class);
        $this->registerUserHandler->register($registerUser);
        $this->assertEmpty($this->userRepository->all());
        $this->assertFalse($this->testEventSubscriber->isUserRegistered);
    }

    /**
     * @test
     */
    public function it_does_not_register_a_new_user_with_invalid_external_user_id()
    {
        $registerUser = new RegisterUser();
        $registerUser->externalUserId = '';
        $registerUser->name = 'valid_name';
        $this->expectException(\Exception::class);
        $this->registerUserHandler->register($registerUser);
        $this->assertEmpty($this->userRepository->all());
        $this->assertFalse($this->testEventSubscriber->isUserRegistered);
    }

    /**
     * @test
     */
    public function it_does_not_register_a_new_user_with_invalid_name()
    {
        $registerUser = new RegisterUser();
        $registerUser->externalUserId = 'valid_external_user_id';
        $registerUser->name = '';
        $this->expectException(\Exception::class);
        $this->registerUserHandler->register($registerUser);
        $this->assertEmpty($this->userRepository->all());
        $this->assertFalse($this->testEventSubscriber->isUserRegistered);
    }
}
