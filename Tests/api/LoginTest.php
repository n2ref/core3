<?php

namespace Tests\Api;

use PHPUnit\Framework\TestCase;
use Viola\Tests\Api\ApiGateway; // Предполагаемый класс для взаимодействия с API

class LoginTest extends TestCase
{
    protected $apiGateway;

    protected function setUp(): void
    {
        parent::setUp();
        // Инициализация API Gateway или HTTP Client
        $this->apiGateway = new ApiGateway(); 
    }

    /**
     * Тес.т успешной авторизации пользователя
     * @return void
     */
    public function testSuccessfulLogin()
    {
        $credentials = [
            'login' => 'testuser@example.com',
            'password' => 'StrongPassword123',
            'fp' => 'dummy_fingerprint'
        ];

        $response = $this->apiGateway->post('/sys/auth/login', $credentials);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertIsString($data['access_token']);
        $this->assertIsString($data['refresh_token']);
    }

    /**
     * Тест авторизации с некорректным паролем.
     * @return void
     */
    public function testLoginWithInvalidPassword()
    {
        $credentials = [
            'login' => 'testuser@example.com',
            'password' => 'WrongPassword',
            'fp' => 'dummy_fingerprint'
        ];

        $response = $this->apiGateway->post('/sys/auth/login', $credentials);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Некорректные данные', $data['message']);
    }

    /**
     * Тест авторизации без обязательного поля 'password'.
     * @return void
     */
    public function testLoginMissingPassword()
    {
        $credentials = [
            'login' => 'testuser@example.com',
            // 'password' отсутствует
            'fp' => 'dummy_fingerprint'
        ];

        $response = $this->apiGateway->post('/sys/auth/login', $credentials);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Некорректные данные', $data['message']);
    }
}