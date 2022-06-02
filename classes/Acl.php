<?php
namespace Core3\Classes;
use Laminas\Permissions;


/**
 *
 */
class Acl extends Db {

    const PRIVILEGE_READ   = 'read';
    const PRIVILEGE_EDIT   = 'edit';
    const PRIVILEGE_DELETE = 'delete';


    /**
     * @var Auth|null
     */
    protected $auth = null;

    /**
     * @var Permissions\Acl\Acl|null
     */
    private static $acl = null;

    /**
     * @var array
     */
    private array $privileges_default = [
        self::PRIVILEGE_READ,
        self::PRIVILEGE_EDIT,
        self::PRIVILEGE_DELETE,
    ];


    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct() {
        $this->auth = Registry::has('auth') ? Registry::get('auth') : null;
    }


    /**
     * @return bool
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
	public function setupAcl(): bool {

        $cache_key = 'core3_acl_' . $this->auth->getRoleId();

		if ($this->cache->test($cache_key)) {
            self::$acl = $this->cache->load($cache_key);

        } else {
            if ( ! $this->auth->isAdmin()) {
                $modules = $this->db->fetchAll("
                    SELECT m.name, 
                           m.privileges
                    FROM core_modules AS m
                    WHERE m.is_active_sw = 'Y'
                    ORDER BY m.seq
                ");

                $sections = $this->db->fetchAll("
                    SELECT ms.name, 
                           m.privileges,
                           m.name AS module_name
                    FROM core_modules_sections AS ms
                        JOIN core_modules AS m ON ms.module_id = m.id
                    WHERE ms.is_active_sw = 'Y' 
                      AND m.is_active_sw = 'Y'
                    ORDER BY m.seq, ms.seq
                ");

                $role = $this->db->fetchRow("
                    SELECT name, 
                           privileges
                    FROM core_roles
                    WHERE id = ?
                ", $this->auth->getRoleId());


                self::$acl = new Permissions\Acl\Acl();
                self::$acl->addRole(new Permissions\Acl\Role\GenericRole($this->auth->getRoleId()));


                $resources = [];

                foreach ($modules as $module) {
                    $resources[$module['name']] = $module['privileges']
                        ? json_decode($module['privileges'], true)
                        : [];

                    self::$acl->addResource(new Permissions\Acl\Resource\GenericResource($module['name']));
                }

                foreach ($sections as $section) {
                    $resource             = "{$section['module_name']}_{$section['name']}";
                    $resources[$resource] = $section['privileges']
                        ? json_decode($section['privileges'], true)
                        : [];

                    self::$acl->addResource(new Permissions\Acl\Resource\GenericResource($resource), $section['module_name']);
                }


                $role_privileges = $role['privileges'] ? json_decode($role['privileges'], true) : [];

                if ( ! empty($resources)) {
                    foreach ($resources as $resource => $privileges) {

                        // Установка дефолтных привилегий
                        foreach ($this->privileges_default as $privilege_default) {

                            if ( ! empty($role_privileges[$resource]) &&
                                in_array($privilege_default, $role_privileges[$resource])
                            ) {
                                self::$acl->allow($this->auth->getRoleId(), $resource, $privilege_default);
                            } else {
                                self::$acl->deny($this->auth->getRoleId(), $resource, $privilege_default);
                            }
                        }


                        // Установка привилегий из модулей
                        foreach ($privileges as $privilege) {

                            if (empty($privilege['name'])) {
                                continue;
                            }

                            if ( ! empty($role_privileges[$resource]) &&
                                in_array($privilege['name'], $role_privileges[$resource])
                            ) {
                                self::$acl->allow($this->auth->getRoleId(), $resource, $privilege['name']);
                            } else {
                                self::$acl->deny($this->auth->getRoleId(), $resource, $privilege['name']);
                            }
                        }
                    }
                }
            }


            $this->cache->save($cache_key, self::$acl, ["core3_acl", "core3_acl_" . $this->auth->getRoleId()]);
        }

        return true;
	}


    /**
     * Разрешить использование ресурса $resource для роли $role с привилегиями $type
     * @param string $resource
     * @param string $type
     * @return bool
     */
	public function allow(string $resource, string $type): bool {

        if ($this->auth) {
            return false;
        }

        if ( ! self::$acl->hasResource($resource)) {
            return false;
        }

        self::$acl->allow($this->auth->getRoleId(), $resource, $type);

        return true;
	}


    /**
     * Доступ роли к ресурсу по всем параметрам, за исключением тех, что указаны в $except
     * @param string $resource
     * @return bool
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
	public function allowAll(string $resource): bool {

        if ($this->auth) {
            return false;
        }

        if ( ! self::$acl->hasResource($resource)) {
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

        if ($this->auth) {
            return false;
        }

        if ( ! self::$acl->hasResource($resource)) {
            return false;
        }

        self::$acl->deny($this->auth->getRoleId(), $resource, $privilege);

        return true;
    }


    /**
     * Проверка доступа к ресурсу $source для текущей роли
     * @param string $resource
     * @param string $privilege
     * @return bool
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
	public function isAllowed(string $resource, string $privilege = self::PRIVILEGE_READ): bool {

        if ( ! $this->auth) {
            return false;
        }

		if ($this->auth->isAdmin()) {
			return true;

		} elseif (self::$acl->hasResource($resource)) {
			return self::$acl->isAllowed($this->auth->getRoleId(), $resource, $privilege);

		} else {
			return false;
		}
	}
}