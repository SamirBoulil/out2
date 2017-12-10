<?php

namespace OnceUponATime\Application;

use OnceUponATime\Domain\Event\UserRegistered;

interface UserRegisteredNotify
{
    public function userRegistered(UserRegistered $event): void;
}
