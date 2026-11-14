<?php
namespace Rowan\Classes;

use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\Store;

class Cache {

    private const TAG_PREFIX = '__tag:';

    private Repository $repository;


    /**
     * @param Store $store
     */
    public function __construct(Store $store) {

        $this->repository = new Repository($store);
    }


    /**
     * @return Repository
     */
    public function getAdapter(): Repository {

        return $this->repository;
    }


    /**
     * @param string $key
     * @return bool
     */
    public function test(string $key): bool {

        return $this->safe(fn () => $this->repository->has($key), false);
    }


    /**
     * Индекс "тэг => ключи" ведётся вручную: Cache::tags() не работает с FileStore
     */
    public function setTags(string $key, array $tags): bool {

        return $this->safe(function () use ($key, $tags) {
            foreach ($tags as $tag) {
                $index_key = self::TAG_PREFIX . $tag;
                $keys      = $this->repository->get($index_key, []);

                if ( ! in_array($key, $keys, true)) {
                    $keys[] = $key;
                    $this->repository->forever($index_key, $keys);
                }
            }

            return true;
        }, false);
    }


    /**
     * @param string $key
     * @return mixed
     */
    public function load(string $key): mixed {

        return $this->safe(fn () => $this->repository->get($key), null);
    }


    /**
     * @param string                 $key
     * @param mixed                  $data
     * @param array                  $tags
     * @param int|\DateInterval|null $ttl секунды; null - хранить бессрочно
     * @return void
     */
    public function save(string $key, mixed $data, array $tags = [], int|\DateInterval|null $ttl = null): void {

        $this->safe(function () use ($key, $data, $tags, $ttl) {
            if ($ttl === null) {
                $this->repository->forever($key, $data);
            } else {
                $this->repository->put($key, $data, $ttl);
            }

            if ($tags) {
                $this->setTags($key, $tags);
            }
        });
    }


    /**
     * Без ключа очищает всё хранилище целиком (flush)
     * @param string $key
     * @return void
     */
    public function clear(string $key = ''): void {

        $this->safe(function () use ($key) {
            if ($key) {
                $this->repository->forget($key);
            } else {
                $this->repository->flush();
            }
        });
    }


    /**
     * @param array $tags
     * @return void
     */
    public function clearByTags(array $tags): void {

        $this->safe(function () use ($tags) {
            foreach ($tags as $tag) {
                $index_key = self::TAG_PREFIX . $tag;
                $keys      = $this->repository->get($index_key, []);

                foreach ($keys as $key) {
                    $this->repository->forget($key);
                }

                $this->repository->forget($index_key);
            }
        });
    }


    /**
     * @param string|array $key
     * @return void
     */
    public function remove(string|array $key): void {

        $this->safe(function () use ($key) {
            foreach ((array)$key as $item) {
                $this->repository->forget($item);
            }
        });
    }


    /**
     * Аналог Laminas ExceptionHandler с setThrowExceptions(false):
     * ошибки хранилища не пробрасываются, возвращается значение по умолчанию
     */
    private function safe(callable $callback, mixed $default = null): mixed {

        try {
            return $callback();
        } catch (\Throwable) {
            return $default;
        }
    }
}