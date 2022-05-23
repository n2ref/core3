<?php
namespace Core3\Classes;

/**
 *
 */
class System {

    /**
     * Перевод текста
     * @param string $text
     * @param string $domain
     * @return string|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function _(string $text, string $domain = 'core3'): ?string {

        $translate = Registry::has('translate') ? Registry::get('translate') : null;

        return $translate?->tr($text, $domain);
    }
}