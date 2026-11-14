<?php

namespace Tests\Api;

use PHPUnit\Framework\TestCase;
use Viola\Tests\Api\ApiGateway; // Предполагаемый класс для взаимодействия с API

class LogoutTest extends TestCase
{
    protected $apiGateway;

    protected function setUp(): void
    {
        parent::setUp();
        // Инициализация API Gateway или HTTP Client
        $this->apiGateway = new ApiGateway(); 
    }

    /**
     * Тест успешного выхода из системы.
     * @return void
     */
    public function testSuccessfulLogout()
    {
        // Для выхода обычно требуется токен доступа в заголовках
        $headers = [
            'Authorization' => 'Bearer valid_access_token'
        ];

        $response = $this->apiGateway->put('/sys/auth/logout', null, $headers);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Сообщение о выходе', $data['message']);
    }

    /**
     * Тест выхода из системы без токена (ожидается 401).
     * @return void
     */
    public function testLogoutWithoutToken()
    {
        // Отправляем запрос без заголовка авторизации
        $response = $this->apiGateway->put('/sys/auth/logout', null, []);

        $this->assertEquals(401, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Не авторизован', $data['message']);
    }
}