<?php

namespace Tests\Api;

use PHPUnit\Framework\TestCase;
use Viola\Tests\Api\ApiGateway; // Предполагаемый класс для взаимодействия с API

class ModuleInitTest extends TestCase
{
    protected $apiGateway;

    protected function setUp(): void
    {
        parent::setUp();
        // Инициализация API Gateway или HTTP Client
        $this->apiGateway = new ApiGateway(); 
    }

    /**
     * Тест успешной инициализации модуля.
     * @return void
     */
    public function testSuccessfulModuleInitialization()
    {
        $moduleName = 'test_module';
        $response = $this->apiGateway->get("/{$moduleName}");

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        // Проверяем, что ответ содержит данные инициализации модуля
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    /**
     * Тест запроса к несуществующему модулю (ожидается 404).
     * @return void
     */
    public function testModuleNotFound()
    {
        $moduleName = 'nonexistent_module';
        $response = $this->apiGateway->get("/{$moduleName}");

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Модуль не найден', $data['message']);
    }
}