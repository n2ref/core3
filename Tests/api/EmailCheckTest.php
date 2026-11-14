<?php

namespace Tests\Api;

use PHPUnit\Framework\TestCase;
use Viola\Tests\Api\ApiGateway; // Предполагаемый класс для взаимодействия с API

class EmailCheckTest extends TestCase
{
    protected $apiGateway;

    protected function setUp(): void
    {
        parent::setUp();
        // Инициализация API Gateway или HTTP Client
        $this->apiGateway = new ApiGateway(); 
    }

    /**
     * Тест проверки доступности email для регистрации (доступен).
     * @return void
     */
    public function testEmailIsAvailable()
    {
        $params = [
            'email' => 'available@example.com'
        ];

        $response = $this->apiGateway->post('/sys/registration/email/check', $params);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('available', $data);
        $this->assertTrue($data['available']);
    }

    /**
     * Тест проверки доступности email для регистрации (занят).
     * @return void
     */
    public function testEmailIsAlreadyRegistered()
    {
        $params = [
            'email' => 'taken@example.com'
        ];

        $response = $this->apiGateway->post('/sys/registration/email/check', $params);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('available', $data);
        $this->assertFalse($data['available']);
    }

    /**
     * Тест запроса с некорректными данными (отсутствует email).
     * @return void
     */
    public function testEmailCheckMissingEmail()
    {
        $params = [
            // 'email' отсутствует
        ];

        $response = $this->apiGateway->post('/sys/registration/email/check', $params);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Некорректные данные', $data['message']);
    }
}