<?php

declare(strict_types=1);

namespace OnceUponATime\Application;

use OnceUponATime\Domain\Entity\ExternalUserId;
use OnceUponATime\Domain\Entity\QuestionId;
use OnceUponATime\Domain\Repository\QuestionRepositoryInterface;
use OnceUponATime\Domain\Repository\UserRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AnswerQuestionHandler
{
    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function handle(AnswerQuestion $answerQuestion): bool
    {
        $question = $this->questionRepository->byId(QuestionId::fromString($answerQuestion->questionId));
        $answer = ExternalUserId::fromString($answerQuestion->answer);

        return $question->isCorrect($answer);
    }
}
