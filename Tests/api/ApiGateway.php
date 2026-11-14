<?php
namespace Viola\Tests\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * ApiGateway
 *
 * Класс-клиент для взаимодействия с API Gateway в тестовых сценариях.
 * Использует Guzzle HTTP Client для выполнения запросов.
 */
class ApiGateway
{
    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @var string
     * Базовый URI для всех запросов.
     */
    protected string $baseUrl;

    /**
     * Конструктор класса.
     *
     * @param string $baseUrl Базовый URI API Gateway.
     * @param Client|null $client Инициализированный Guzzle Client. Если null, будет создан новый.
     */
    public function __construct(string $baseUrl, ?Client $client = null)
    {
        $this->baseUrl = $baseUrl;
        $this->client = $client ?? new Client([
            'base_uri' => $baseUrl,
            'timeout' => 10.0,
        ]);
    }

    /**
     * Выполняет GET запрос к указанному эндпоинту.
     *
     * @param string $endpoint Эндпоинт API (например, 'users/profile').
     * @param array $query Параметры запроса (query parameters).
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function get(string $endpoint, array $query = []): ResponseInterface
    {
        try {
            $response = $this->client->request('GET', $endpoint, [
                'query' => $query,
            ]);
            return $response;
        } catch (GuzzleException $e) {
            // Логирование или обработка ошибки
            throw $e;
        }
    }

    /**
     * Выполняет POST запрос к указанному эндпоинту.
     *
     * @param string $endpoint Эндпоинт API.
     * @param array $data Данные для отправки в теле запроса (body).
     * @param array $headers Дополнительные заголовки.
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function post(string $endpoint, array $data, array $headers = []): ResponseInterface
    {
        try {
            $response = $this->client->request('POST', $endpoint, [
                'json' => $data, // Отправка данных в формате JSON
                'headers' => $headers,
            ]);
            return $response;
        } catch (GuzzleException $e) {
            // Логирование или обработка ошибки
            throw $e;
        }
    }

    /**
     * Выполняет PUT запрос для обновления ресурса.
     *
     * @param string $endpoint Эндпоинт API.
     * @param array $data Данные для обновления.
     * @param array $headers Дополнительные заголовки.
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function put(string $endpoint, array $data, array $headers = []): ResponseInterface
    {
        try {
            $response = $this->client->request('PUT', $endpoint, [
                'json' => $data,
                'headers' => $headers,
            ]);
            return $response;
        } catch (GuzzleException $e) {
            // Логирование или обработка ошибки
            throw $e;
        }
    }

    /**
     * Выполняет DELETE запрос для удаления ресурса.
     *
     * @param string $endpoint Эндпоинт API.
     * @param array $headers Дополнительные заголовки.
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function delete(string $endpoint, array $headers = []): ResponseInterface
    {
        try {
            $response = $this->client->request('DELETE', $endpoint, [
                'headers' => $headers,
            ]);
            return $response;
        } catch (GuzzleException $e) {
            // Логирование или обработка ошибки
            throw $e;
        }
    }

    /**
     * Получает тело ответа как декодированный массив.
     *
     * @param ResponseInterface $response
     * @return array
     * @throws \Exception Если тело ответа не является JSON.
     */
    public function getBodyArray(ResponseInterface $response): array
    {
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to decode JSON response body: " . json_last_error_msg());
        }
        return $data;
    }
}
