<?php

namespace Tests\Api;

use PHPUnit\Framework\TestCase;
use Viola\Tests\Api\ApiGateway; // Предполагаемый класс для взаимодействия с API

class CabinetTest extends TestCase
{
    protected $apiGateway;

    protected function setUp(): void
    {
        parent::setUp();
        // Инициализация API Gateway или HTTP Client
        $this->apiGateway = new ApiGateway(); 
    }

    /**
     * Тест успешного получения данных личного кабинета.
     * @return void
     */
    public function testGetCabinetData()
    {
        // Для доступа к кабинету требуется токен
        $headers = [
            'Authorization' => 'Bearer valid_access_token'
        ];

        $response = $this->apiGateway->get('/sys/cabinet', null, $headers);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        // Проверяем, что ответ является объектом и содержит данные кабинета
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    /**
     * Тест доступа к кабинету без токена (ожидается 401).
     * @return void
     */
    public function testGetCabinetDataWithoutToken()
    {
        // Отправляем запрос без заголовка авторизации
        $response = $this->apiGateway->get('/sys/cabinet');

        $this->assertEquals(401, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Не авторизован', $data['message']);
    }

    /**
     * Тест доступа к кабинету без прав (ожидается 403).
     * @return void
     */
    public function testGetCabinetDataWithoutPermission()
    {
        // Предполагаем, что токен есть, но прав недостаточно
        $headers = [
            'Authorization' => 'Bearer restricted_token'
        ];

        $response = $this->apiGateway->get('/sys/cabinet', null, $headers);

        $this->assertEquals(403, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Нет доступа', $data['message']);
    }
}