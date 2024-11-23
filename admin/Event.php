<?php
namespace Core3\Mod\Admin;
use Core3\Classes\Common;
use Laminas\Cache\Exception\ExceptionInterface;


/**
 *
 */
class Event extends Common {

    /**
     * @param string $event_name
     * @param array $data
     * @return void
     * @throws ExceptionInterface
     */
    public function admin(string $event_name, array $data): void {

        if ($event_name == 'role_update') {
            $cache_key = "core3_acl_{$data['role_id']}";

            if ($this->cache->test($cache_key)) {
                $this->cache->clear($cache_key);
            }
        }
    }
}