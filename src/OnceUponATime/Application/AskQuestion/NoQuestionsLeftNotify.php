<?php

declare(strict_types=1);

namespace OnceUponATime\Application\AskQuestion;

use OnceUponATime\Domain\Event\NoQuestionsLeft;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface NoQuestionsLeftNotify
{
    public function noQuestionsLeft(NoQuestionsLeft $event): void;
}
