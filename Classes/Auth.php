<?php
namespace Core3\Classes;


/**
 *
 */
class Auth {

    private array $session = [];
    private array $user    = [];


    public function __construct(array $user, array $session = []) {
        $this->user    = $user;
        $this->session = $session;
    }


    /**
     * @return array
     */
    public function getSession(): array {
        return $this->session;
    }


    /**
     * @return array
     */
    public function getUser(): array {
        return $this->user;
    }


    /**
     * @return string
     */
    public function getUserName(): string {

        $fname = $this->user['fname'] ?? '';
        $lname = $this->user['lname'] ?? '';
        $mname = $this->user['mname'] ?? '';

        return trim("{$lname} {$fname} {$mname}");
    }


    /**
     * @return bool
     */
    public function isAdmin(): bool {

        return ! empty($this->user['is_admin_sw']) && $this->user['is_admin_sw'] == 'Y';
    }


    /**
     * @return string
     */
    public function getRefreshToken(): string {

        return (string)($this->session['refresh_token'] ?? '');
    }
}