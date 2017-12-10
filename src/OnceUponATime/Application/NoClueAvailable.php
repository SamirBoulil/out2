<?php

declare(strict_types=1);

namespace OnceUponATime\Application;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NoClueAvailable extends \LogicException
{
    /** @var string */
    private $userId;

    /** @var string */
    private $externalUserId;

    public static function fromString(string $userId, string $externalUserId): NoClueAvailable
    {
        $e = new self(sprintf('There is no clue available for external user id "%s".', $externalUserId));
        $e->userId = $userId;
        $e->externalUserId = $externalUserId;

        return $e;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getExternalUserId(): string
    {
        return $this->externalUserId;
    }

}
