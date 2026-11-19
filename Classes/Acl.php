<?php
namespace Rowan\Classes;


/**
 *
 */
class Acl {

    const TYPE_ALLOW = 'allow';
    const TYPE_DENY  = 'deny';

    private array $roles     = [];
    private array $resources = [];
    private array $rules     = [];


    /**
     * Добавление роли
     * @param int|string            $role
     * @param array|int|string|null $parents
     * @return self
     */
    public function addRole(int|string $role, array|int|string|null $parents = null): self {

        $this->roles[(string)$role] = $parents !== null
            ? array_map('strval', (array)$parents)
            : [];

        return $this;
    }


    /**
     * Проверка существования роли
     * @param int|string $role
     * @return bool
     */
    public function hasRole(int|string $role): bool {

        return isset($this->roles[(string)$role]);
    }


    /**
     * Добавление ресурса
     * @param string      $resource
     * @param string|null $parent
     * @return self
     * @throws \InvalidArgumentException
     */
    public function addResource(string $resource, string|null $parent = null): self {

        if ($this->hasResource($resource)) {
            throw new \InvalidArgumentException("Resource id '{$resource}' already exists in the ACL");
        }

        if ($parent !== null && ! $this->hasResource($parent)) {
            throw new \InvalidArgumentException("Parent Resource id '{$parent}' does not exist");
        }

        $this->resources[$resource] = ['parent' => $parent, 'children' => []];

        if ($parent !== null) {
            $this->resources[$parent]['children'][$resource] = true;
        }

        return $this;
    }


    /**
     * Проверка существования ресурса
     * @param string $resource
     * @return bool
     */
    public function hasResource(string $resource): bool {

        return isset($this->resources[$resource]);
    }


    /**
     * Разрешить привилегию $privilege для роли $role на ресурсе $resource
     * @param int|string  $role
     * @param string      $resource
     * @param string|null $privilege null - все привилегии
     * @return self
     */
    public function allow(int|string $role, string $resource, string|null $privilege = null): self {

        return $this->setRule(self::TYPE_ALLOW, $role, $resource, $privilege);
    }


    /**
     * Запретить привилегию $privilege для роли $role на ресурсе $resource
     * @param int|string  $role
     * @param string      $resource
     * @param string|null $privilege null - все привилегии
     * @return self
     */
    public function deny(int|string $role, string $resource, string|null $privilege = null): self {

        return $this->setRule(self::TYPE_DENY, $role, $resource, $privilege);
    }


    /**
     * Проверка доступа роли к привилегии ресурса
     * @param int|string  $role
     * @param string      $resource
     * @param string|null $privilege null - все привилегии
     * @return bool
     */
    public function isAllowed(int|string $role, string $resource, string|null $privilege = null): bool {

        if ( ! $this->hasRole($role) || ! $this->hasResource($resource)) {
            return false;
        }

        $roles = $this->getRoleChain((string)$role);

        if ($privilege !== null) {
            foreach ($roles as $role_id) {
                $type = $this->rules[$resource][$role_id]['privileges'][$privilege] ?? null;

                if ($type !== null) {
                    return $type === self::TYPE_ALLOW;
                }

                $type = $this->rules[$resource][$role_id]['all'] ?? null;

                if ($type !== null) {
                    return $type === self::TYPE_ALLOW;
                }
            }

            return false;
        }

        foreach ($roles as $role_id) {
            $rule = $this->rules[$resource][$role_id] ?? [];

            foreach ($rule['privileges'] ?? [] as $type) {
                if ($type === self::TYPE_DENY) {
                    return false;
                }
            }

            if (isset($rule['all'])) {
                return $rule['all'] === self::TYPE_ALLOW;
            }
        }

        return false;
    }


    /**
     * Установка правила с распространением на дочерние ресурсы
     * @param string      $type
     * @param int|string  $role
     * @param string      $resource
     * @param string|null $privilege
     * @return self
     */
    private function setRule(string $type, int|string $role, string $resource, string|null $privilege): self {

        foreach ($this->getResourceChain($resource) as $resource_id) {

            if ($privilege === null) {
                $this->rules[$resource_id][(string)$role]['all'] = $type;
            } else {
                $this->rules[$resource_id][(string)$role]['privileges'][$privilege] = $type;
            }
        }

        return $this;
    }


    /**
     * Цепочка ролей: сама роль и её родители
     * @param string $role
     * @param array  $visited
     * @return array
     */
    private function getRoleChain(string $role, array &$visited = []): array {

        if (isset($visited[$role])) {
            return [];
        }

        $visited[$role] = true;
        $chain          = [$role];

        foreach (array_reverse($this->roles[$role] ?? []) as $parent) {
            foreach ($this->getRoleChain($parent, $visited) as $item) {
                $chain[] = $item;
            }
        }

        return $chain;
    }


    /**
     * Цепочка ресурсов: сам ресурс и его дочерние ресурсы
     * @param string $resource
     * @return array
     */
    private function getResourceChain(string $resource): array {

        $chain = [$resource];

        foreach (array_keys($this->resources[$resource]['children'] ?? []) as $child) {
            foreach ($this->getResourceChain($child) as $item) {
                $chain[] = $item;
            }
        }

        return $chain;
    }
}