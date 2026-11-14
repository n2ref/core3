<?php
namespace Rowan\Admin\Classes;
use Rowan\Classes\Common;
use Laminas\Cache\Exception\ExceptionInterface;


/**
 *
 */
class Events extends Common {


    /**
     * Обновление роли
     * @param array $data
     * @return void
     * @throws ExceptionInterface
     */
    public function roleUpdate(array $data): void {

        if (empty($data['role_id'])) {
            return;
        }

        $cache_key = "rowan_acl_{$data['role_id']}";

        if ($this->cache->test($cache_key)) {
            $this->cache->clear($cache_key);
        }
    }
}