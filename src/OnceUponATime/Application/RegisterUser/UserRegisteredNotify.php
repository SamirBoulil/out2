<?php

namespace OnceUponATime\Application\RegisterUser;

use OnceUponATime\Domain\Event\UserRegistered;

interface UserRegisteredNotify
{
    public function userRegistered(UserRegistered $event): void;
}
