<?php

try {
    require 'bootstrap.php';
    echo (new \Core3\Classes\Init())->dispatch();

} catch (\Exception $e) {
    \Core3\Classes\Error::catchException($e);
}