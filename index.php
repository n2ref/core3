<?php

try {
    require 'bootstrap.php';
    $init = new \Core3\Sys\Init();
    $init->auth();

    echo $init->dispatch();

} catch (\Exception $e) {
    \Core3\Sys\Error::catchException($e);
}