<?php
namespace Core3\Classes\Http;
use Core3\Classes;
use Core3\Exceptions\HttpException;


/**
 *
 */
abstract class Common extends Classes\Common {

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

        $token = new Token((string)$sign, (string)$algorithm);
        $token->set($user_login, $session_id, $refresh_token_exp);

        return $token;
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

        $token = new Token($sign, $algorithm);
        $token->set($user_login, $session_id, $access_token_exp);

        return $token;
    }
}