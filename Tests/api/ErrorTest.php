<?php

namespace Tests\Api;

use PHPUnit\Framework\TestCase;
use Viola\Tests\Api\ApiGateway; // Предполагаемый класс для взаимодействия с API

class ErrorTest extends TestCase
{
    protected $apiGateway;

    protected function setUp(): void
    {
        parent::setUp();
        // Инициализация API Gateway или HTTP Client
        $this->apiGateway = new ApiGateway(); 
    }

    /**
     * Тест успешной записи ошибки в систему логирования.
     * @return void
     */
    public function testSuccessfulErrorLogging()
    {
        $payload = [
            'message' => 'Не удалось обработать запрос пользователя.',
            'trace' => ['File', 'Line', 'Function'] // Пример стека вызовов
        ];

        $response = $this->apiGateway->post('/sys/error', $payload);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('Успешно', $data['status']);
    }

    /**
     * Тест отправки ошибки с некорректным форматом данных (отсутствует message).
     * @return void
     */
    public function testErrorLoggingMissingMessage()
    {
        $payload = [
            // 'message' отсутствует
            'trace' => ['File', 'Line', 'Function']
        ];

        $response = $this->apiGateway->post('/sys/error', $payload);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Некорректные данные', $data['message']);
    }
}