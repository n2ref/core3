<?php
namespace Core3\Sys;
use Laminas\Permissions;


/**
 *
 */
class Auth {

    const PRIVILEGE_READ   = 'read';
    const PRIVILEGE_EDIT   = 'edit';
    const PRIVILEGE_DELETE = 'delete';

    /**
     * @var Permissions\Acl\Acl
     */
    private Permissions\Acl\Acl $acl;

    private array $session = [];
    private array $user    = [];


    /**
     * @param array $user
     * @param array $session
     */
    public function __construct(array $user, array $session = []) {

        $this->user    = $user;
        $this->session = $session;
        $this->acl     = new Permissions\Acl\Acl();
    }


    /**
     * Получение данных сессии
     * @return array
     */
    public function getSession(): array {
        return $this->session;
    }


    /**
     * Получение данных пользователя
     * @return array
     */
    public function getUser(): array {
        return $this->user;
    }


    /**
     * Получение id пользователя
     * @return int
     */
    public function getUserId(): int {

        return (int)($this->user['id'] ?? 0);
    }


    /**
     * Получение логина пользователя
     * @return string
     */
    public function getUserLogin(): string {

        return (string)($this->user['login'] ?? '');
    }


    /**
     * Получение email пользователя
     * @return string
     */
    public function getUserEmail(): string {

        return (string)($this->user['email'] ?? '');
    }


    /**
     * Получение имени пользователя
     * @return string
     */
    public function getUserName(): string {

        $fname = $this->user['fname'] ?? '';
        $lname = $this->user['lname'] ?? '';

        return trim("{$lname} {$fname}");
    }


    /**
     * Является ли пользователь админом
     * @return bool
     */
    public function isAdmin(): bool {

        return ! empty($this->user['is_admin']) && $this->user['is_admin'] == '1';
    }


    /**
     * Получение id роли
     * @return int
     */
    public function getRoleId(): int {

        return $this->user['role_id'] ?? 0;
    }


    /**
     * Получение refresh_token пользователя
     * @return string
     */
    public function getRefreshToken(): string {

        return (string)($this->session['refresh_token'] ?? '');
    }


    /**
     * Получение id сессии
     * @return int
     */
    public function getSessionId(): int {

        return (int)($this->session['id'] ?? 0);
    }


    /**
     * Установка привилегий
     * @param Permissions\Acl\Acl $acl
     * @return void
     */
    public function setAcl(Permissions\Acl\Acl $acl): void {

        $this->acl = $acl;
    }


    /**
     * Разрешить использование ресурса $resource для роли $role с привилегиями $type
     * @param string $resource
     * @param string $type
     * @return bool
     */
    public function allow(string $resource, string $type): bool {

        if ( ! $this->acl->hasResource($resource)) {
            return false;
        }

        $this->acl->allow($this->getRoleId(), $resource, $type);

        return true;
    }


    /**
     * Доступ роли к ресурсу по всем параметрам, за исключением тех, что указаны в $except
     * @param string $resource
     * @return bool
     */
    public function allowAll(string $resource): bool {

        if ( ! $this->acl->hasResource($resource)) {
            return false;
        }

        $privileges = [
            self::PRIVILEGE_READ,
            self::PRIVILEGE_EDIT,
            self::PRIVILEGE_DELETE
        ];

        foreach ($privileges as $privilege) {
            $this->allow($resource, $privilege);
        }
        return true;
    }


    /**
     * Запретить использование ресурса $resource для роли $role с привилегиями $type
     * @param string $resource
     * @param string $privilege
     * @return bool
     */
    public function deny(string $resource, string $privilege): bool {

        if ( ! $this->acl->hasResource($resource)) {
            return false;
        }

        $this->acl->deny($this->getRoleId(), $resource, $privilege);

        return true;
    }


    /**
     * Проверка доступа к ресурсу $source для текущей роли
     * @param string $resource
     * @param string $privilege
     * @return bool
     */
    public function isAllowed(string $resource, string $privilege = self::PRIVILEGE_READ): bool {

        if ($this->isAdmin()) {
            return true;

        } elseif ($this->acl->hasResource($resource)) {
            return $this->acl->isAllowed($this->getRoleId(), $resource, $privilege);

        } else {
            return false;
        }
    }
}