<?php
namespace Rowan\Classes;
use Illuminate\Cache\FileStore;
use Illuminate\Cache\Repository;
use Illuminate\Filesystem\Filesystem;

use Illuminate\Cache\MemcachedConnector;
use Illuminate\Cache\MemcachedStore;
use Illuminate\Cache\RedisStore;
use Illuminate\Container\Container;
use Illuminate\Redis\RedisManager;




/**
 * @property-read Config $config
 * @property-read Cache  $cache
 * @property-read Log    $log
 */
class System {

    private   string       $version      = '0.3.0';
    protected static array $static_cache = [];


    /**
     * @return string
     */
    protected function getSystemVersion(): string {

        return $this->version;
    }


    /**
     * Перевод текста
     * @param string $text
     * @param array  $params
     * @return string|null
     */
    public static function _(string $text, array $params = []): ?string {

        $translate      = Registry::has('translate') ? Registry::get('translate') : null;
        $translate_text = $translate?->tr($text, $params, 'core');

        $func_params = [$translate_text];

        if ( ! empty($params)) {
            foreach ($params as $param) {
                $func_params[] = $param;
            }
        }

        return call_user_func_array('sprintf', $func_params);
    }


    /**
     * @param string $param_name
     * @return mixed
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     * @throws \Exception
     */
    public function __get(string $param_name) {

        $result = null;

        if ($this->hasStaticCache($param_name)) {
            $result = $this->getStaticCache($param_name);

        } else {
            switch ($param_name) {
                case 'config':
                    $result = Registry::has('config') ? Registry::get('config') : null;
                    break;

                case 'cache':
                    $cache_config = $this->config?->system?->cache;
                    $adapter_name = $cache_config?->adapter ?? 'Filesystem';
                    $options      = $cache_config?->options?->toArray() ?? [];

                    switch ($adapter_name) {
                        case 'Filesystem':
                            if (empty($options['dir'])) {
                                throw new \Exception($this->_('В файле конфиге не указана папка для кэша: system.cache.options.dir'));
                            }

                            $store = new FileStore(new Filesystem(), $options['dir']);
                            break;

                        case 'Redis':
                            $server = $options['server'] ?? [];
                            $client = $options['client'] ?? 'phpredis';

                            if ( ! in_array($client, ['phpredis', 'predis'], true)) {
                                throw new \Exception($this->_('Неизвестный клиент Redis для кэша: %s', $client));
                            }

                            $redis = new RedisManager(new Container(), $client, [
                                'default' => [
                                    'host'     => $server['host']     ?? '127.0.0.1',
                                    'port'     => (int)($server['port'] ?? 6379),
                                    'password' => $server['password'] ?? null,
                                    'database' => (int)($server['database'] ?? 0),
                                ],
                            ]);


                            $host = $this->config?->system?->host
                                ?: ($_SERVER['SERVER_NAME'] ?? null)
                                    ?: (gethostname() ?: 'rowan');

                            $store = new RedisStore($redis, "{$host}:Rowan:", 'default');
                            break;

                        case 'Memcached':
                            $servers = $options['servers'] ?? [
                                [
                                    'host'   => $options['host'] ?? '127.0.0.1',
                                    'port'   => $options['port'] ?? 11211,
                                    'weight' => 100,
                                ],
                            ];

                            $memcached = (new MemcachedConnector())->connect($servers);
                            $store     = new MemcachedStore($memcached, 'Rowan:');
                            break;

                        default:
                            throw new \Exception($this->_(sprintf('Указанный адаптер для кэша не найден: %s', $adapter_name)));
                    }

                    $result = new Cache($store);
                    break;

                case 'log':
                    $name = $this->config?->system?->host ?: ($_SERVER['SERVER_NAME'] ?? 'rowan');
                    $result = new Log($name);
                    break;
            }

            if ( ! is_null($result)) {
                $this->setStaticCache($param_name, $result);
            }
        }

        return $result;
    }


    /**
     * Режим "только чтение" для основной базы
     * @param string $connection_name
     * @return bool
     */
    public function isReadOnlyDb(string $connection_name = 'base'): bool {

        $readonly = $this->config?->system?->db?->{$connection_name}?->readonly;

        return is_null($readonly) || $readonly;
    }


    /**
     * @param string $name
     * @return bool
     */
    protected function hasStaticCache(string $name): bool {

        return isset(self::$static_cache[$name]);
    }


    /**
     * @param string $name
     * @return mixed
     */
    protected function getStaticCache(string $name): mixed {

        return $this->hasStaticCache($name) ? self::$static_cache[$name] : null;
    }


    /**
     * @param string $name
     * @param mixed  $value
     * @return void
     */
    protected function setStaticCache(string $name, mixed $value): void {

        self::$static_cache[$name] = $value;
    }
}