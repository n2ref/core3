<?php

namespace Tests\Api;

use PHPUnit\Framework\TestCase;
use Viola\Tests\Api\ApiGateway; // Предполагаемый класс для взаимодействия с API

class ConfigTest extends TestCase
{
    protected $apiGateway;

    protected function setUp(): void
    {
        parent::setUp();
        // Инициализация API Gateway или HTTP Client
        $this->apiGateway = new ApiGateway(); 
    }

    /**
     * Тест успешного получения конфигурации системы.
     * @return void
     */
    public function testGetSystemConfiguration()
    {
        $response = $this->apiGateway->get('/sys/conf');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        // Проверяем, что ответ является объектом и содержит данные конфигурации
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }
}