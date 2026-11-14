<?php

namespace Tests\Api;

use PHPUnit\Framework\TestCase;
use Viola\Tests\Api\ApiGateway; // Предполагаемый класс для взаимодействия с API

class HomeTest extends TestCase
{
    protected $apiGateway;

    protected function setUp(): void
    {
        parent::setUp();
        // Инициализация API Gateway или HTTP Client
        $this->apiGateway = new ApiGateway(); 
    }

    /**
     * Тест успешного получения главной страницы кабинета.
     * @return void
     */
    public function testGetHomePage()
    {
        // Требуется токен для доступа к главной странице
        $headers = [
            'Authorization' => 'Bearer valid_access_token'
        ];

        $response = $this->apiGateway->get('/sys/home', null, $headers);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        // Проверяем, что ответ является объектом и содержит данные главной страницы
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    /**
     * Тест доступа к главной странице без токена (ожидается 401).
     * @return void
     */
    public function testGetHomePageWithoutToken()
    {
        // Отправляем запрос без заголовка авторизации
        $response = $this->apiGateway->get('/sys/home');

        $this->assertEquals(401, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Не авторизован', $data['message']);
    }
}