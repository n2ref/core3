<?php
namespace Core3\Classes;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SlackWebhookHandler;


/**
 * Обеспечение журналирования запросов пользователей
 * и других событий
 * Class Logger
 * @method slack($channel, $username)
 */
class Log {

    private Logger $log;
    private Config $core_config;
    private string $writer_custom;
    private array  $handlers;


    /**
     * @param string $name
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    public function __construct(string $name = 'core3') {

        $this->core_config = Registry::has('core_config') ? Registry::get('core_config') : null;
        $this->log         = new Logger($name);


        if ($this->core_config?->log &&
            $this->core_config?->log?->system &&
            $this->core_config?->log?->system?->file
        ) {
            $stream = new StreamHandler($this->core_config->log->system->file);
            $this->log->pushHandler($stream);
        }
    }


    /**
     * Обработчик метода не доступного через экземпляр
     * @param string    $name       Имя метода
     * @param array     $arguments  Параметры метода
     * @return object|null
     */
    public function __call(string $name, array $arguments = []) {

        if ($name == 'slack') {
            if ( ! $this->core_config?->log ||
                 ! $this->core_config?->log?->webhook?->slack
            ) {
                return new \stdClass();
            }

            $channel                = $arguments[0] ?? null;
            $username               = $arguments[1] ?? null;
            $useAttachment          = true;
            $iconEmoji              = null;
            $useShortAttachment     = false;
            $includeContextAndExtra = false;
            $level                  = Logger::CRITICAL;
            $bubble                 = true;
            $excludeFields          = [];

            $this->handlers[$name] = [
                $this->core_config->log->webhook->slack?->url,
                $channel,
                $username,
                $useAttachment,
                $iconEmoji,
                $useShortAttachment,
                $includeContextAndExtra,
                $level,
                $bubble,
                $excludeFields,
            ];

            return $this;
        }

        return null;
    }


    /**
     * Дополнительный лог в заданный файл
     * @param string $filename
     * @return $this
     * @throws \Exception
     */
    public function file(string $filename): self {

        if ( ! $this->writer_custom) {
            $this->log->pushHandler(new StreamHandler($filename));
            $this->writer_custom = $filename;
        }

        return $this;
    }


    /**
     * Информационная запись в лог
     * @param string $message
     * @param array  $context
     */
    public function info(string $message, array $context = []): void {

        if ($this->handlers) {
            $this->setHandler(Logger::INFO);
        }

        $this->log->info($message, $context);
        $this->removeCustomWriter();
    }


    /**
     * Предупреждение в лог
     * @param string $message
     * @param array  $context
     */
    public function warning(string $message, array $context = []): void {

        if ($this->handlers) {
            $this->setHandler(Logger::WARNING);
        }

        $this->log->warning($message, $context);
        $this->removeCustomWriter();
    }


    /**
     * Предупреждение в лог
     * @param string $message
     * @param array  $context
     */
    public function error(string $message, array $context = []): void {

        if ($this->handlers) {
            $this->setHandler(Logger::ERROR);
        }

        $this->log->warning($message, $context);
        $this->removeCustomWriter();
    }


    /**
     * Отладочная информация в лог
     * @param string $message
     * @param array  $context
     */
    public function debug(string $message, array $context = []): void {

        if ($this->handlers) {
            $this->setHandler(Logger::DEBUG);
        }

        $this->log->warning($message, $context);
        $this->removeCustomWriter();
    }


    /**
     * Прекращение записи в заданный дополнительный лог
     */
    private function removeCustomWriter(): void {

        if ($this->writer_custom) {
            $this->log->popHandler();
            $this->writer_custom = '';
        }
    }


    /**
     * Установка обработчика
     * @param int $level Уровень журналирования
     */
    private function setHandler(int $level): void {

        while ($this->log->getHandlers()) {
            $this->log->popHandler();
        }

        foreach ($this->handlers as $name => $params) {
            if ($name == 'slack') {
                $handler = new SlackWebhookHandler($params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6], $level);
                $this->log->pushHandler($handler);
            }
        }
    }
}