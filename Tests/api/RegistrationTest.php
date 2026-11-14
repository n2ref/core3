<?php

namespace Tests\Api;

use PHPUnit\Framework\TestCase;
use Viola\Tests\Api\ApiGateway; // Предполагаемый класс для взаимодействия с API

class RegistrationTest extends TestCase
{
    protected $apiGateway;

    protected function setUp(): void
    {
        parent::setUp();
        // Инициализация API Gateway или HTTP Client
        $this->apiGateway = new ApiGateway(); 
    }

    /**
     * Тест успешной регистрации нового пользователя.
     * @return void
     */
    public function testSuccessfulRegistration()
    {
        $credentials = [
            'email' => 'newuser@example.com',
            'lname' => 'NewLastName',
            'code' => '123456',
            'password' => 'StrongPassword123',
            'fp' => 'dummy_fingerprint'
        ];

        $response = $this->apiGateway->post('/sys/registration/email', $credentials);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertIsString($data['access_token']);
        $this->assertIsString($data['refresh_token']);
    }

    /**
     * Тест регистрации с уже существующим email.
     * @return void
     */
    public function testRegistrationWithExistingEmail()
    {
        $credentials = [
            'email' => 'existing@example.com',
            'lname' => 'NewLastName',
            'code' => '123456',
            'password' => 'StrongPassword123',
            'fp' => 'dummy_fingerprint'
        ];

        $response = $this->apiGateway->post('/sys/registration/email', $credentials);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Email уже зарегистрирован', $data['message']);
    }

    /**
     * Тест регистрации с некорректными данными (например, отсутствует пароль).
     * @return void
     */
    public function testRegistrationMissingPassword()
    {
        $credentials = [
            'email' => 'newuser@example.com',
            'lname' => 'NewLastName',
            'code' => '123456',
            // 'password' отсутствует
            'fp' => 'dummy_fingerprint'
        ];

        $response = $this->apiGateway->post('/sys/registration/email', $credentials);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Некорректные данные', $data['message']);
    }
}