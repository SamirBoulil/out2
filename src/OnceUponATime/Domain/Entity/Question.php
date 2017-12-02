<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Entity;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Question
{
    /** @var QuestionId */
    private $id;

    /** @var Statement */
    private $statement;

    /** @var ExternalUserId */
    private $answer;

    /** @var Clue */
    private $clue1;

    /** @var Clue */
    private $clue2;

    public static function ask(
        QuestionId $questionId,
        Statement $statement,
        ExternalUserId $answer,
        Clue $clue1,
        Clue $clue2
    ): Question {
        $question = new self();
        $question->id = $questionId;
        $question->statement = $statement;
        $question->answer = $answer;
        $question->clue1 = $clue1;
        $question->clue2 = $clue2;

        return $question;
    }

    public function id(): QuestionId
    {
        return $this->id;
    }

    public function answer(): ExternalUserId
    {
        return $this->answer;
    }

    public function clue1(): Clue
    {
        return $this->clue1;
    }

    public function clue2(): Clue
    {
        return $this->clue2;
    }

    public function isCorrect(ExternalUserId $answer): bool
    {
        return $this->answer()->equals($answer);
    }
}
