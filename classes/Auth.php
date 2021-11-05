<?php
namespace Core3\Classes;

require_once 'Db.php';


/**
 *
 */
class Auth extends Db {

    private $session;

    /**
     * @throws \Exception
     */
    public function __construct($session) {
        parent::__construct();

    }


    /**
     *
     */
    public function getSession() {


    }


    /**
     *
     */
    public function getUser() {


    }


    public function isMobile(): bool {

    }


    public function isAdmin() {


    }


    public function getRefrashToken() {


    }
}