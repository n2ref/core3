<?php

namespace Tests\Api;

use PHPUnit\Framework\TestCase;
use Viola\Tests\Api\ApiGateway; // Предполагаемый класс для взаимодействия с API

class RestoreTest extends TestCase
{
    protected $apiGateway;

    protected function setUp(): void
    {
        parent::setUp();
        // Инициализация API Gateway или HTTP Client
        $this->apiGateway = new ApiGateway(); 
    }

    /**
     * Тест успешного запроса на восстановление пароля.
     * @return void
     */
    public function testSuccessfulPasswordRecoveryRequest()
    {
        $credentials = [
            'email' => 'user@example.com',
            'fp' => 'dummy_fingerprint'
        ];

        $response = $this->apiGateway->post('/sys/restore', $credentials);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Запрос на восстановление отправлен', $data['message']);
    }

    /**
     * Тест запроса на восстановление пароля с некорректным email.
     * @return void
     */
    public function testRestoreRequestWithInvalidEmail()
    {
        $credentials = [
            'email' => 'nonexistent@example.com',
            'fp' => 'dummy_fingerprint'
        ];

        $response = $this->apiGateway->post('/sys/restore', $credentials);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Некорректные данные', $data['message']);
    }
}