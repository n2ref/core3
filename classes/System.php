<?php
namespace Core3\Classes;
use Laminas\Cache\Storage;


/**
 * @property-read Config $config
 * @property-read Cache  $cache
 * @property-read Log    $log
 */
abstract class System {

    private   string       $version      = '3.0.0';
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
     * @param string $domain
     * @return string|null
     */
    public static function _(string $text, array $params = [], string $domain = 'core3'): ?string {

        $translate      = Registry::has('translate') ? Registry::get('translate') : null;
        $translate_text = $translate?->tr($text, $domain);

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
                    $adapter_name = $this->config?->cache?->adapter ?? 'Filesystem';
                    $options      = $this->config?->cache?->options->toArray() ?? [];

                    if ($this->config?->cache?->adapter) {
                        $adapter_name = $this->config?->cache?->adapter;
                        $options      = $this->config?->cache?->options->toArray() ?? [];
                    }

                    switch ($adapter_name) {
                        case 'Filesystem':
                            $options['namespace'] = "Core3";
                            $adapter              = new Storage\Adapter\Filesystem($options);
                            break;

                        case 'Redis':
                            $host                 = $this->config?->system?->host ?? ($_SERVER['SERVER_NAME'] ?? '');
                            $options['namespace'] = "{$host}:Core3";
                            $adapter              = new Storage\Adapter\Redis($options);
                            break;

                        case 'Memcached':
                            $options['namespace'] = "Core3";
                            $adapter              = new Storage\Adapter\Memcached($options);
                            break;

                        default:
                            throw new \Exception($this->_(sprintf('Указанный адаптер для кэша не найден: %s', $adapter_name)));
                    }


                    $plugin = new Storage\Plugin\ExceptionHandler();
                    $plugin->getOptions()->setThrowExceptions(false);

                    $adapter->addPlugin(new Storage\Plugin\Serializer());
                    $adapter->addPlugin($plugin);

                    $result = new Cache($adapter);
                    break;

                case 'log':
                    $name = $this->config?->system?->host ?: ($_SERVER['SERVER_NAME'] ?? 'core3');
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