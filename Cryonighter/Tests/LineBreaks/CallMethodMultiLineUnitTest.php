<?php

/**
 * This file errors example
 */

$user = User::create()->update();
$user->setName($name)->setAge($age);
$user = User::create()->update()->setName($name)->setAge($age);
