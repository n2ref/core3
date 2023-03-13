<?php
namespace Core3\Classes;
use Exception;
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
    private Config $config;
    private string $writer_default = '';
    private string $writer_custom  = '';
    private array  $handlers       = [];


    /**
     * @param string $name
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws Exception
     */
    public function __construct(string $name = 'core3') {

        $this->config = Registry::has('config') ? Registry::get('config') : null;
        $this->log    = new Logger($name);

        if ($this->config?->system?->log?->on &&
            $this->config?->system?->log?->file
        ) {
            $this->writer_default = $this->getAbsolutePath($this->config->system->log->file);
        }
    }


    /**
     * Обработчик метода не доступного через экземпляр
     * @param string    $name       Имя метода
     * @param array     $arguments  Параметры метода
     * @return mixed
     */
    public function __call(string $name, array $arguments = []): mixed {

        if ($name == 'slack') {
            if ( ! $this->config?->log ||
                 ! $this->config?->log?->webhook?->slack
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
                $this->config->log->webhook->slack?->url,
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
     */
    public function file(string $filename): self {

        $this->writer_custom = $this->getAbsolutePath($filename);

        return $this;
    }


    /**
     * Информационная запись в лог
     * @param string $message
     * @param array  $context
     * @throws Exception
     */
    public function info(string $message, array $context = []): void {

        if ($this->handlers) {
            $this->setHandler(Logger::INFO);
        }

        $this->setWriter();
        $this->log->info($message, $context);
        $this->removeWriter();
    }


    /**
     * Предупреждение в лог
     * @param string $message
     * @param array  $context
     * @throws Exception
     */
    public function warning(string $message, array $context = []): void {

        if ($this->handlers) {
            $this->setHandler(Logger::WARNING);
        }

        $this->setWriter();
        $this->log->warning($message, $context);
        $this->removeWriter();
    }


    /**
     * Предупреждение в лог
     * @param string $message
     * @param array  $context
     * @throws Exception
     */
    public function error(string $message, array $context = []): void {

        if ($this->handlers) {
            $this->setHandler(Logger::ERROR);
        }

        $this->setWriter();
        $this->log->warning($message, $context);
        $this->removeWriter();
    }


    /**
     * Отладочная информация в лог
     * @param string $message
     * @param array  $context
     * @throws Exception
     */
    public function debug(string $message, array $context = []): void {

        if ($this->handlers) {
            $this->setHandler(Logger::DEBUG);
        }

        $this->setWriter();
        $this->log->warning($message, $context);
        $this->removeWriter();
    }


    /**
     * @return void
     * @throws Exception
     */
    private function setWriter(): void {

        if ($this->writer_custom) {
            $this->log->pushHandler(new StreamHandler($this->writer_custom));

        } elseif ($this->writer_default) {
            $this->log->pushHandler(new StreamHandler($this->writer_default));
        }
    }


    /**
     * Прекращение записи в заданный лог
     */
    private function removeWriter(): void {

        if ($this->writer_custom) {
            $this->log->popHandler();
            $this->writer_custom = '';

        } elseif ($this->writer_default) {
            $this->log->popHandler();
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


    /**
     * @param string $file_path
     * @return string
     */
    private function getAbsolutePath(string $file_path): string {

        if (substr($file_path, 0, 1) === '/') {
            return $file_path;
        }

        $file_path = trim($file_path, '/');

        return DOC_ROOT . "/{$file_path}";
    }
}