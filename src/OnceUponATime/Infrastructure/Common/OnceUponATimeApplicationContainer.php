<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Common;

use OnceUponATime\Application\AnswerQuestion\AnswerQuestionHandler;
use OnceUponATime\Application\AnswerQuestion\QuestionAnsweredNotify;
use OnceUponATime\Application\AskQuestion\AskQuestionHandler;
use OnceUponATime\Application\AskQuestion\QuestionAskedNotify;
use OnceUponATime\Application\AskQuestion\QuizCompletedNotify;
use OnceUponATime\Application\RegisterUser\RegisterUserHandler;
use OnceUponATime\Application\RegisterUser\UserRegisteredNotify;
use OnceUponATime\Application\ShowClue\ShowClueHandler;
use OnceUponATime\Application\ShowLeaderboard\ShowLeaderboardHandler;
use OnceUponATime\Application\ShowQuestion\ShowQuestionHandler;
use OnceUponATime\Domain\Event\QuizEventStore;
use OnceUponATime\Domain\Repository\QuestionRepository;
use OnceUponATime\Domain\Repository\UserRepository;
use OnceUponATime\Infrastructure\Notifications\NewQuestionForNewUser;
use OnceUponATime\Infrastructure\Notifications\NewQuestionForUserWhoAnsweredCorrectly;
use OnceUponATime\Infrastructure\Notifications\NewQuestionForUserWhoAnsweredIncorrectly;
use OnceUponATime\Infrastructure\Notifications\PublishToEventStore;
use OnceUponATime\Infrastructure\Notifications\QuestionAnsweredNotifyMany;
use OnceUponATime\Infrastructure\Notifications\QuestionAskedNotifyMany;
use OnceUponATime\Infrastructure\Notifications\QuizCompletedNotifyMany;
use OnceUponATime\Infrastructure\Notifications\UserRegisteredNotifyMany;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuestionRepository;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuizEventStore;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryUserRepository;
use OnceUponATime\Infrastructure\UI\CLI\RegisterUserConsoleHandler;
use OnceUponATime\Infrastructure\UI\CLI\ShowQuestionConsoleHandler;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OnceUponATimeApplicationContainer implements ContainerInterface
{
    public function __construct()
    {
        $this->container = $this->buildContainer();
    }

    public function get($id)
    {
        return $this->container->get($id);
    }

    public function has($id): bool
    {
        return $this->container->has($id);
    }

    public function getConsoleApplication(): Application
    {
        return $this->container->get(Application::class);
    }

    private function buildContainer(): ContainerInterface
    {
        $containerBuilder = new ContainerBuilder();

        /**
         * Persistence
         */
        $containerBuilder->register(UserRepository::class, InMemoryUserRepository::class)
            ->setPublic(true);
        $containerBuilder->register(QuestionRepository::class, InMemoryQuestionRepository::class)
            ->setPublic(true);
        $containerBuilder->register(QuizEventStore::class, InMemoryQuizEventStore::class)
            ->setPublic(true);

        /**
         * Notify
         */
        $containerBuilder
            ->register(UserRegisteredNotify::class, UserRegisteredNotifyMany::class)
            ->addArgument([new Reference(NewQuestionForNewUser::class)]);
        $containerBuilder->register(NewQuestionForNewUser::class, NewQuestionForNewUser::class)
            ->addArgument(new Reference(AskQuestionHandler::class));

        $containerBuilder
            ->register(QuestionAnsweredNotify::class, QuestionAnsweredNotifyMany::class)
            ->addArgument(new Reference(PublishToEventStore::class))
            ->addArgument(new Reference(NewQuestionForUserWhoAnsweredCorrectly::class))
            ->addArgument(new Reference(NewQuestionForUserWhoAnsweredIncorrectly::class));
        $containerBuilder->register(PublishToEventStore::class, PublishToEventStore::class)
            ->addArgument(new Reference(QuizEventStore::class));
        $containerBuilder->register(NewQuestionForUserWhoAnsweredCorrectly::class, NewQuestionForUserWhoAnsweredCorrectly::class)
            ->addArgument(new Reference(AskQuestionHandler::class));
        $containerBuilder->register(NewQuestionForUserWhoAnsweredIncorrectly::class,
            NewQuestionForUserWhoAnsweredIncorrectly::class)
            ->addArgument(new Reference(QuizEventStore::class))
            ->addArgument(new Reference(AskQuestionHandler::class));

        $containerBuilder
            ->register(QuestionAskedNotify::class, QuestionAskedNotifyMany::class)
            ->addArgument([new Reference(PublishToEventStore::class)]);

        $containerBuilder
            ->register(QuizCompletedNotify::class, QuizCompletedNotifyMany::class)
            ->addArgument([new Reference(PublishToEventStore::class)]);

        /**
         * Handler
         */
        $containerBuilder
            ->register(AnswerQuestionHandler::class, AnswerQuestionHandler::class)
            ->addArgument(new Reference(UserRepository::class))
            ->addArgument(new Reference(QuestionRepository::class))
            ->addArgument(new Reference(QuizEventStore::class))
            ->addArgument(new Reference(QuestionAnsweredNotify::class));

        $containerBuilder
            ->register(AskQuestionHandler::class, AskQuestionHandler::class)
            ->addArgument(new Reference(UserRepository::class))
            ->addArgument(new Reference(QuestionRepository::class))
            ->addArgument(new Reference(QuizEventStore::class))
            ->addArgument(new Reference(QuizCompletedNotify::class))
            ->addArgument(new Reference(QuestionAskedNotify::class));

        $containerBuilder
            ->register(RegisterUserHandler::class, RegisterUserHandler::class)
            ->addArgument(new Reference(UserRepository::class))
            ->addArgument(new Reference(UserRegisteredNotify::class));

        $containerBuilder
            ->register(ShowClueHandler::class, ShowClueHandler::class)
            ->addArgument(new Reference(UserRepository::class))
            ->addArgument(new Reference(QuestionRepository::class))
            ->addArgument(new Reference(QuizEventStore::class));

        $containerBuilder
            ->register(ShowLeaderboardHandler::class, ShowLeaderboardHandler::class)
            ->addArgument(new Reference(UserRepository::class))
            ->addArgument(new Reference(QuizEventStore::class));

        $containerBuilder
            ->register(ShowQuestionHandler::class, ShowQuestionHandler::class)
            ->addArgument(new Reference(UserRepository::class))
            ->addArgument(new Reference(QuestionRepository::class))
            ->addArgument(new Reference(QuizEventStore::class));

        /**
         * Command
         */
        $containerBuilder
            ->register(RegisterUserConsoleHandler::class, RegisterUserConsoleHandler::class)
            ->addArgument(new Reference(RegisterUserHandler::class));

        $containerBuilder
            ->register(ShowQuestionConsoleHandler::class, ShowQuestionConsoleHandler::class)
            ->addArgument(new Reference(ShowQuestionHandler::class));

        /**
         * Application
         */
        $containerBuilder
            ->register(Application::class, Application::class)
            ->addMethodCall(
                'addCommands',
                [
                    [
                        new Reference(RegisterUserConsoleHandler::class),
                        new Reference(ShowQuestionConsoleHandler::class),
                    ],
                ]
            )
            ->setPublic(true);

        $containerBuilder->compile();

        return $containerBuilder;
    }
}
