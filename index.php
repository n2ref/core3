<?php

try {
    require 'bootstrap.php';

    $init = new \Core3\Init();
    $init->auth();
    echo $init->dispatch();

} catch (\Exception $e) {
    \Core3\Error::catchException($e);
}