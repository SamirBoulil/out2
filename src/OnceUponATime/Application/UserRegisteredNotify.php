<?php

namespace OnceUponATime\Application;

use OnceUponATime\Domain\Entity\UserRegistered;

interface UserRegisteredNotify
{
    public function userRegistered(UserRegistered $event): void;
}
