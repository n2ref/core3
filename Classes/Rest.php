<?php
namespace Core3\Classes;
use Core3\Mod\Admin;
use Core3\Exceptions\HttpException;

use JetBrains\PhpStorm\ArrayShape;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;


/**
 * @property Admin\Controller $modAdmin
 */
class Rest extends Common {

    private const RP = '8c1733d4cd0841199aa02ec9362be324';



    /**
     * Авторизация по email
     * @param array $params
     * @return array
     * @throws \Exception
     * @throws \Zend_Db_Adapter_Exception|\Zend_Exception
     * @OA\Post(
     *   path    = "/client/auth/email",
     *   tags    = { "Доступ" },
     *   summary = "Авторизация по email",
     *   @OA\RequestBody(
     *     description = "Данные для входа",
     *     required    = true,
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(type = "object", example = { "email" = "client@gmail.com", "password" = "197nmy4t70yn3v285v2n30304m3v204304" })
     *     )
     *   ),
     *   @OA\Response(
     *     response    = "200",
     *     description = "Вебтокен клиента",
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(type = "object", example = { "wetoken" = "xxxxxxxxxxxxxx" } )
     *     )
     *   ),
     *   @OA\Response(
     *     response    = "400",
     *     description = "Отправленные данные некорректны",
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(ref = "#/components/schemas/Error")
     *     )
     *   )
     * )
     */
    #[ArrayShape(['refresh_token' => "string", 'access_token' => "string"])]
    public function login(array $params): array {

        HttpValidator::testParameters([
            'login'    => 'req,string(1-255)',
            'password' => 'req,string(1-255)',
        ], $params);


        $user = $this->modAdmin->dataUsers->getRowByLoginEmail($params['login']);

        if ($user) {
            if ($user->is_active_sw == 'N') {
                throw new HttpException('Этот пользователь деактивирован', 'user_inactive', 400);
            }

            if ($user->password != Tools::pass_salt($params['password'])) {
                throw new HttpException('Неверный пароль', 'pass_incorrect', 400);
            }

        } else {
            throw new HttpException('Пользователя с таким логином нет', 'login_not_found', 400);
        }


        $refresh_token = $this->getRefreshToken($user->id, $user->login);
        $access_token  = $this->getAccessToken($user->id, $user->login);
        $exp           = $refresh_token->claims()->get('exp');

        $user_session = $this->modAdmin->dataUsersSession->createRow([
            'user_id'            => $user->id,
            'refresh_token'      => $refresh_token->toString(),
            'client_ip'          => $_SERVER['REMOTE_ADDR'] ?? '',
            'agent_name'         => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'date_expired'       => date('Y-m-d H:i:s', $exp->getTimestamp()),
            'date_last_activity' => new \Zend_Db_Expr('NOW()'),
        ]);
        $user_session->save();



        setcookie("Core-Refresh-Token", $refresh_token, time() + 157680000, '/core', null, false);

        return [
            'refresh_token' => $refresh_token->toString(),
            'access_token'  => $access_token->toString(),
        ];
    }


    /**
     * Общая проверка аутентификации
     * @return bool
     * @throws \Exception
     */
    private function auth(): bool {

        // проверяем, есть ли в запросе токен
        $access_token = '';
        if ( ! empty($_SERVER['HTTP_AUTHORIZATION'])) {
            if (strpos('Bearer', $_SERVER['HTTP_AUTHORIZATION']) === 0) {
                $access_token = $_SERVER['HTTP_AUTHORIZATION'];
            }

        } else if ( ! empty($_SERVER['HTTP_ACCESS_TOKEN'])) {
            $access_token = $_SERVER['HTTP_ACCESS_TOKEN'];
        }


        $auth = $access_token
            ? $this->getAuthByToken($access_token)
            : null;

        if ($auth) {
            $this->auth = $auth;
            Registry::set('auth', $this->auth);
            return true;
        }

        return false;
    }


    /**
     * Авторизация по токену
     * @param string $access_token
     * @return Auth|null
     */
    private function getAuthByToken(string $access_token): ?Auth {

        try {
            $sign          = $this->config->system->auth->token->sign;
            $configuration = Configuration::forSymmetricSigner(new Sha256(), $sign);

            $token_jwt  = $configuration->parser()->parse((string)$access_token);
            $token_exp  = $token_jwt->claims()->get('exp');
            $session_id = $token_jwt->claims()->get('sid');

            if (empty($token_exp) || empty($session_id)) {
                return null;
            }

            $now = date_create();
            if ($now > $token_exp) {
                return null;
            }


            $session = $this->modAdmin->dataSession->find($session_id)->current();

            if (empty($session) || $session->is_active_sw == 'N') {
                return null;
            }


            $user = $this->modAdmin->dataUsers->find($session->user_id)->current();

            if (empty($user) && $user->is_active_sw == 'N') {
                return null;
            }

            $session->date_last_activity = new \Zend_Db_Expr('NOW()');
            $session->save();

            return new Auth($user->toArray(), $session->toArray());

        } catch (\Exception $e) {
            // ignore
        }

        return null;
    }


    /**
     * @param int    $user_id
     * @param string $user_login
     * @return \Lcobucci\JWT\Token\Plain
     */
    private function getRefreshToken(int $user_id, string $user_login): \Lcobucci\JWT\Token\Plain {

        $expiration = 86400; // Сутки
        $sign       = '';

        if ($this->config?->system?->auth?->refresh_token?->expiration) {
            $expiration = (int)$this->config->system->auth->refresh_token->expiration;
        }
        if ($this->config?->system?->auth?->token_sign) {
            $sign = (int)$this->config->system->auth->token->token_sign;
        }


        if ($sign) {
            $configuration = Configuration::forSymmetricSigner(new Sha256(), Key\InMemory::plainText($sign));
        } else {
            $configuration = Configuration::forUnsecuredSigner();
        }




        $now   = new \DateTimeImmutable();
        return $configuration->builder()
            // Configures the issuer (iss claim)
            ->issuedBy($_SERVER['SERVER_NAME'] ?? '')
            // Configures the id (jti claim)
            ->identifiedBy($user_id)
            // Configures the time that the token was issue (iat claim)
            ->issuedAt($now)
            // Configures the expiration time of the token (exp claim)
            ->expiresAt($now->modify("+{$expiration} second"))
            ->withHeader('aud', $user_login)
            // Builds a new token
            ->getToken($configuration->signer(), $configuration->signingKey());
    }


    /**
     * @param int    $user_id
     * @param string $user_login
     * @return \Lcobucci\JWT\Token\Plain
     */
    private function getAccessToken(int $user_id, string $user_login): \Lcobucci\JWT\Token\Plain {

        $expiration = 86400; // Сутки
        $sign       = '';

        if ($this->config?->system?->auth?->access_token?->expiration) {
            $expiration = (int)$this->config->system->auth->access_token->expiration;
        }
        if ($this->config?->system?->auth?->token_sign) {
            $sign = (int)$this->config->system->auth->token->token_sign;
        }


        if ($sign) {
            $configuration = Configuration::forSymmetricSigner(new Sha256(), Key\InMemory::plainText($sign));
        } else {
            $configuration = Configuration::forUnsecuredSigner();
        }




        $now   = new \DateTimeImmutable();
        return $configuration->builder()
            // Configures the issuer (iss claim)
            ->issuedBy($_SERVER['SERVER_NAME'] ?? '')
            // Configures the id (jti claim)
            ->identifiedBy($user_id)
            // Configures the time that the token was issue (iat claim)
            ->issuedAt($now)
            // Configures the expiration time of the token (exp claim)
            ->expiresAt($now->modify("+{$expiration} second"))
            ->withHeader('aud', $user_login)
            // Builds a new token
            ->getToken($configuration->signer(), $configuration->signingKey());
    }
}