<?php

namespace Tests\Api;

use PHPUnit\Framework\TestCase;
use Viola\Tests\Api\ApiGateway; // Предполагаемый класс для взаимодействия с API

class RefreshTest extends TestCase
{
    protected $apiGateway;

    protected function setUp(): void
    {
        parent::setUp();
        // Инициализация API Gateway или HTTP Client
        $this->apiGateway = new ApiGateway(); 
    }

    /**
     * Тест успешного обновления токенов.
     * @return void
     */
    public function testSuccessfulTokenRefresh()
    {
        $credentials = [
            'refresh_token' => 'valid_refresh_token',
            'fp' => 'dummy_fingerprint'
        ];

        $response = $this->apiGateway->post('/sys/auth/refresh', $credentials);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertIsString($data['access_token']);
        $this->assertIsString($data['refresh_token']);
    }

    /**
     * Тест обновления токенов с неверным refresh_token.
     * @return void
     */
    public function testTokenRefreshWithInvalidToken()
    {
        $credentials = [
            'refresh_token' => 'invalid_refresh_token',
            'fp' => 'dummy_fingerprint'
        ];

        $response = $this->apiGateway->post('/sys/auth/refresh', $credentials);

        $this->assertEquals(403, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Неверный токен', $data['message']);
    }

    /**
     * Тест обновления токенов с отсутствующим fp.
     * @return void
     */
    public function testTokenRefreshMissingFp()
    {
        $credentials = [
            'refresh_token' => 'valid_refresh_token',
            // 'fp' отсутствует
        ];

        $response = $this->apiGateway->post('/sys/auth/refresh', $credentials);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Некорректные данные', $data['message']);
    }
}