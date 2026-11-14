<?php

namespace Tests\Api;

use PHPUnit\Framework\TestCase;
use Viola\Tests\Api\ApiGateway; // Предполагаемый класс для взаимодействия с API

class ModuleSectionTest extends TestCase
{
    protected $apiGateway;

    protected function setUp(): void
    {
        parent::setUp();
        // Инициализация API Gateway или HTTP Client
        $this->apiGateway = new ApiGateway(); 
    }

    /**
     * Тест успешной обработки секции модуля.
     * @return void
     */
    public function testSuccessfulModuleSectionProcessing()
    {
        $module = 'test_module';
        $section = 'test_section';
        $params = 'query_param';

        // Тест с параметрами запроса
        $response = $this->apiGateway->get("/{$module}/{$section}?{$params}");

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        // Проверяем, что ответ содержит данные секции модуля
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    /**
     * Тест обработки несуществующей секции модуля (ожидается 404 или 403).
     * @return void
     */
    public function testModuleSectionNotFound()
    {
        $module = 'test_module';
        $section = 'nonexistent_section';

        $response = $this->apiGateway->get("/{$module}/{$section}");

        // В схеме указан 401/403, но для "не найдено" логичнее 404
        // Будем проверять 403, как указано в схеме для отсутствия доступа
        $this->assertEquals(403, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Нет доступа', $data['message']);
    }
}