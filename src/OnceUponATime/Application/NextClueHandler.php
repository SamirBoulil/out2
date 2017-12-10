<?php

declare(strict_types=1);

namespace OnceUponATime\Application;

use OnceUponATime\Domain\Entity\Clue;
use OnceUponATime\Domain\Entity\ExternalUserId;
use OnceUponATime\Domain\Entity\User;
use OnceUponATime\Domain\EventStore\QuestionsAnsweredEventStore;
use OnceUponATime\Domain\Repository\QuestionRepository;
use OnceUponATime\Domain\Repository\UserRepository;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NextClueHandler
{
    /** @var UserRepository */
    private $userRepository;

    /** @var QuestionRepository */
    private $questionRepository;

    /** @var QuestionsAnsweredEventStore */
    private $questionsAnswered;

    public function __construct(
        UserRepository $userRepository,
        QuestionRepository $questionRepository,
        QuestionsAnsweredEventStore $questionsAnswered
    ) {
        $this->userRepository = $userRepository;
        $this->questionRepository = $questionRepository;
        $this->questionsAnswered = $questionsAnswered;
    }

    public function handle(NextClue $nextQuestion): Clue
    {
        $user = $this->getUser($nextQuestion);
        $this->getNextClue($user);
    }

    private function getUser(NextClue $nextClue): User
    {
        $user = $this->userRepository->byExternalId(ExternalUserId::fromString($nextClue->externalUserId));
        if (null === $user) {
            throw InvalidExternalUserId::fromString($nextClue->externalUserId);
        }

        return $user;
    }

    private function getNextClue(User $user): Clue
    {
        $answeredQuestions = $this->questionsAnswered->byUser($user->id());
        if (empty($answeredQuestions)) {
            throw NoClueAvailable::fromString((string) $user->id(), (string)$user->externalUserId());
        }
    }
}
