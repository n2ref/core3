<?php

try {
    require 'bootstrap.php';

    $init = new \Core3\Classes\Init();
    $init->auth();
    echo $init->dispatch();

} catch (\Exception $e) {
    \Core3\Classes\Error::catchException($e);
}