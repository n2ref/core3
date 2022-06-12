<?php
namespace Core3\Classes\Rest;
use Core3\Classes;
use Core3\Exceptions\HttpException;

class Common extends Classes\Common {

    /**
     * @param string $user_login
     * @param int    $session_id
     * @return Token
     * @throws HttpException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function getRefreshToken(string $user_login, int $session_id): Token {

        $refresh_token_exp = $this->config?->system?->auth?->refresh_token?->expiration ?: 7776000; // 90 дней

        if ( ! is_numeric($refresh_token_exp)) {
            throw new HttpException($this->_('Система настроена некорректно. Задайте system.auth.refresh_token.expiration'), 'error_refresh_token', 500);
        }

        $sign      = $this->config?->system?->auth?->token_sign ?: '';
        $algorithm = $this->config?->system?->auth?->algorithm ?: 'HS256';

        return new Token($user_login, $session_id, $refresh_token_exp, [
            'sign'      => $sign,
            'algorithm' => $algorithm,
        ]);
    }


    /**
     * @param string $user_login
     * @param int    $session_id
     * @return Token
     * @throws HttpException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function getAccessToken(string $user_login, int $session_id): Token {

        $access_token_exp  = $this->config?->system?->auth?->access_token?->expiration  ?: 1800; // 30 минут

        if ( ! is_numeric($access_token_exp)) {
            throw new HttpException($this->_('Система настроена некорректно. Задайте system.auth.access_token.expiration'), 'error_access_token', 500);
        }

        $sign      = $this->config?->system?->auth?->token_sign ?: '';
        $algorithm = $this->config?->system?->auth?->algorithm ?: 'HS256';

        return new Token($user_login, $session_id, $access_token_exp, [
            'sign'      => $sign,
            'algorithm' => $algorithm,
        ]);
    }
}