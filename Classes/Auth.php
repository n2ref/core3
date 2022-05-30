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
     * @return int
     */
    public function getUserId(): int {

        return (int)($this->user['id'] ?? 0);
    }


    /**
     * @return string
     */
    public function getUserLogin(): string {

        return (string)($this->user['login'] ?? '');
    }


    /**
     * @return string
     */
    public function getUserEmail(): string {

        return (string)($this->user['email'] ?? '');
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
     * @return int
     */
    public function getRoleId(): int {

        return $this->user['role_id'] ?? 0;
    }


    /**
     * @return string
     */
    public function getRefreshToken(): string {

        return (string)($this->session['refresh_token'] ?? '');
    }


    /**
     * @return int
     */
    public function getSessionId(): int {

        return (int)($this->session['id'] ?? 0);
    }
}