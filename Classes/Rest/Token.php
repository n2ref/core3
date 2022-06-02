<?php
namespace Core3\Classes\Rest;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 *
 */
class Token {

    private \DateTime $date_expired;
    private string    $token = '';


    /**
     * @param string $user_login
     * @param int    $session_id
     * @param int    $expires_seconds
     * @param array  $options
     */
    public function __construct(string $user_login, int $session_id, int $expires_seconds, array $options = []) {

        $this->date_expired = (new \DateTime())->modify("+{$expires_seconds} second");

        $sign      = $options['sign'] ?? null;
        $algorithm = $options['algorithm'] ?? 'HS256';
        $issuer    = $options['iss'] ?? ($_SERVER['SERVER_NAME'] ?? '');

        $this->token = self::encode([
            'iss' => $issuer,
            'aud' => $user_login,
            'sid' => $session_id,
            'iat' => time(),
            'nbf' => time(),
            'exp' => $this->date_expired->getTimestamp(),
        ], $sign, $algorithm);
    }


    /**
     * @param array       $payload
     * @param string      $sign
     * @param string      $algorithm
     * @param string|null $key_id
     * @param array|null  $head
     * @return string
     */
    public static function encode(array $payload, string $sign = '', string $algorithm = '', string $key_id = null, array $head = null): string {

        return JWT::encode($payload, $sign, $algorithm, $key_id, $head);
    }


    /**
     * @param string $token
     * @param string $sign
     * @param string $algorithm
     * @return array
     */
    public static function decode(string $token, string $sign = '', string $algorithm = ''): array {

        return (array)JWT::decode($token, new Key($sign, $algorithm));
    }


    /**
     * @return \DateTime
     */
    public function dateExpired(): \DateTime {

        return $this->date_expired;
    }


    /**
     * @return string
     */
    public function toString(): string {
        return $this->token;
    }


    /**
     * @return string
     */
    public function __toString(): string {
        return $this->toString();
    }
}