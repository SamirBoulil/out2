<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Persistence\File;

use OnceUponATime\Domain\Entity\Question\Answer;
use OnceUponATime\Domain\Entity\Question\Clue;
use OnceUponATime\Domain\Entity\Question\Question;
use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\Question\Statement;
use OnceUponATime\Infrastructure\Persistence\Common\AbstractQuestionRepository;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileBasedQuestionRepository extends AbstractQuestionRepository
{
    private const QUESTION_ID = 'question_id';
    private const STATEMENT = 'statement';
    private const CLUE_1 = 'clue_1';
    private const CLUE_2 = 'clue_2';
    private const ANSWER = 'answer';

    /** @var string */
    private $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function add(Question $question): void
    {
        $questions = $this->all();
        $questions[] = $question;
        $serializedQuestions = $this->serializeQuestions($questions);

        $fp = fopen($this->filePath, 'w');
        fwrite($fp, $serializedQuestions);
        fclose($fp);
    }

    public function all(): array
    {
        $fileContent = file_get_contents($this->filePath);
        if (empty($fileContent)) {
            return [];
        }

        $questions = $this->deserializeQuestions($fileContent);

        return $questions;
    }

    /**
     * @param Question[] $questions
     */
    private function serializeQuestions(array $questions): string
    {
        $normalizedQuestions = [];
        foreach ($questions as $question) {
            $normalizedQuestions[] = [
                self::QUESTION_ID          => (string) $question->id(),
                self::STATEMENT => (string) $question->statement(),
                self::CLUE_1 => (string) $question->clue1(),
                self::CLUE_2 => (string) $question->clue2(),
                self::ANSWER => (string) $question->answer()
            ];
        }

        return json_encode($normalizedQuestions);
    }

    /**
     * @return Question[]
     */
    private function deserializeQuestions(string $fileContent): array
    {
        $normalizedQuestions = json_decode($fileContent, true);
        $questions = [];
        foreach ($normalizedQuestions as $normalizedQuestion) {
            $questions[] = Question::ask(
                QuestionId::fromString($normalizedQuestion[self::QUESTION_ID]),
                Statement::fromString($normalizedQuestion[self::STATEMENT]),
                Answer::fromString($normalizedQuestion[self::ANSWER]),
                Clue::fromString($normalizedQuestion[self::CLUE_1]),
                Clue::fromString($normalizedQuestion[self::CLUE_2])
            );
        }

        return $questions;
    }
}
