<?php
namespace Core3\Classes;
use DiscordHandler\DiscordHandler;
use Monolog\Handler\MissingExtensionException;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\TelegramBotHandler;
use Monolog\Logger;
use Monolog\Level;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SlackWebhookHandler;


/**
 * Журналирование событий
 */
class Log {

    private Logger $logger;
    private Config $config;
    private string $file        = '';
    private string $date_format = "Y-m-d H:i:s.u";
    private array  $handlers    = [];


    /**
     * @param string $name
     */
    public function __construct(string $name = 'core3') {

        $this->config = Registry::has('config') ? Registry::get('config') : null;
        $this->logger = new Logger($name);

        if ($this->config?->system?->log?->on &&
            $this->config?->system?->log?->file
        ) {
            $this->file = $this->config->system->log->file;
        }
    }


    /**
     * Информационная запись в лог
     * @param string $message
     * @param array  $context
     * @throws \Exception
     */
    public function info(string $message, array $context = []): void {

        if ($this->handlers) {
            $this->setHandlers(Level::Info);
        } else {
            $this->setDefaultHandler();
            $this->subscription(Level::Info);
        }

        $this->logger->info($message, $context);
        $this->clearHandlers();
    }


    /**
     * Предупреждение в лог
     * @param string $message
     * @param array  $context
     * @throws \Exception
     */
    public function warning(string $message, array $context = []): void {

        if ($this->handlers) {
            $this->setHandlers(Level::Warning);
        } else {
            $this->setDefaultHandler();
            $this->subscription(Level::Warning);
        }

        $this->logger->warning($message, $context);
        $this->clearHandlers();
    }


    /**
     * Предупреждение в лог
     * @param string          $message
     * @param array|\Exception $context
     * @throws MissingExtensionException
     * @throws \Exception
     */
    public function error(string $message, array|\Exception $context = []): void {

        if ($this->handlers) {
            $this->setHandlers(Level::Error);
        } else {
            $this->setDefaultHandler();
            $this->subscription(Level::Error);
        }

        if ($context instanceof \Exception) {
            $context = [
                'error_message' => $context->getMessage(),
                'file'          => $context->getFile(),
                'file_line'     => $context->getLine(),
                'trace'         => $context->getTraceAsString(),
            ];
        }

        $this->logger->error($message, $context);
        $this->clearHandlers();
    }


    /**
     * Отладочная информация в лог
     * @param string $message
     * @param array  $context
     * @throws \Exception
     */
    public function debug(string $message, array $context = []): void {

        if ($this->handlers) {
            $this->setHandlers(Level::Debug);
        } else {
            $this->setDefaultHandler();
        }

        $this->logger->debug($message, $context);
        $this->clearHandlers();
    }


    /**
     * Лог в заданный файл
     * @param string $filename
     * @return $this
     */
    public function file(string $filename): self {

        $this->handlerFile($filename);

        return $this;
    }


    /**
     * Отправка сообщения в Slack
     * @return self|\stdClass
     * @throws MissingExtensionException
     */
    public function slack(): \stdClass|self {

        if ( ! $this->config?->system?->log?->webhook?->slack?->url ||
             ! is_string($this->config->system->log->webhook->slack->url)
        ) {
            return new \stdClass();
        }


        $handler = new SlackWebhookHandler($this->config->system->log->webhook->slack->url);
        $handler->setFormatter(new LineFormatter(null, $this->date_format));

        $this->handlers[] = $handler;

        return $this;
    }


    /**
     * Отправка сообщения в Slack
     * @return self|\stdClass
     */
    public function discord(): \stdClass|self {

        if ( ! $this->config?->system?->log?->webhook?->discord?->url ||
             ! is_string($this->config->system->log->webhook->discord->url)
        ) {
            return new \stdClass();
        }


        $handler = new DiscordHandler($this->config->system->log->webhook->discord->url);
        $handler->setFormatter(new LineFormatter(null, $this->date_format));

        $this->handlers[] = $handler;

        return $this;
    }


    /**
     * Отправка сообщения в Tg
     * @return self|\stdClass
     * @throws MissingExtensionException
     */
    public function telegram(): \stdClass|self {

        if ( ! $this->config?->system->log?->webhook?->telegram?->apikey ||
             ! $this->config?->system->log?->webhook?->telegram?->channels ||
             ! is_string($this->config->system->log->webhook->telegram->apikey) ||
             ! is_string($this->config->system->log->webhook->telegram->channels)
        ) {
            return new \stdClass();
        }

        $channels = explode(',', $this->config->system->log->webhook->telegram->channels);
        $channels = array_map('trim', $channels);
        $channels = array_filter($channels);

        if (empty($channels)) {
            return new \stdClass();
        }

        foreach ($channels as $channel) {
            $handler = new TelegramBotHandler($this->config->system->log->webhook->telegram->apikey, $channel);
            $handler->setFormatter(new LineFormatter(null, $this->date_format));

            $this->handlers[] = $handler;
        }

        return $this;
    }


    /**
     * @return void
     * @throws \Exception
     */
    private function setDefaultHandler(): void {

        if ($this->file) {
            $this->handlerFile($this->file);
        }
    }


    /**
     * Запись лога в файл
     * @param string $filename
     * @return void
     */
    private function handlerFile(string $filename): void {

        $filename = str_starts_with($filename, '/')
            ? $filename
            : "{$this->config->system->log->dir}/{$filename}";

        if ( ! $filename) {
            return;
        }

        if ($this->config?->system->log?->rotate?->interval &&
            is_string($this->config->system->log->rotate->interval) &&
            in_array($this->config->system->log->rotate->interval, ['day', 'month', 'year'])
        ) {
            $interval = match ($this->config->system->log->rotate->interval) {
                'day'   => RotatingFileHandler::FILE_PER_DAY,
                'month' => RotatingFileHandler::FILE_PER_MONTH,
                'year'  => RotatingFileHandler::FILE_PER_YEAR,
            };

            $max_files = $this->config?->system?->log?->rotate?->max_files &&
                         is_numeric($this->config->system->log->rotate->max_files) &&
                         $this->config->system->log->rotate->max_files >= 0
                ? (int)$this->config->system->log->rotate->max_files
                : 2;

            $handler = new RotatingFileHandler($filename, $max_files);
            $handler->setFilenameFormat('{filename}-{date}', $interval);

        } else {
            $handler = new StreamHandler($filename);
        }


        $handler->setFormatter(new LineFormatter(null, $this->date_format));

        $this->handlers[] = $handler;
    }


    /**
     * Удаление заданных ранее дополнительных обработчиков
     * @return void
     */
    private function clearHandlers(): void {

        $this->handlers = [];

        $this->logger->setHandlers([]);
    }


    /**
     * Установка обработчиков
     * @param Level $level Уровень
     * @return void
     */
    private function setHandlers(Level $level): void {

        foreach ($this->handlers as $handler) {
            $handler->setLevel($level);
            $this->logger->pushHandler($handler);
        }
    }


    /**
     * Подписка на события
     * @param Level $level
     * @return void
     * @throws MissingExtensionException
     */
    private function subscription(Level $level): void {

        if ($this->config?->system?->log?->subscribe?->level &&
            $this->config?->system?->log?->subscribe?->recipients &&
            is_string($this->config->system->log->subscribe->level) &&
            is_string($this->config->system->log->subscribe->recipients)
        ) {
            switch ($this->config->system->log->subscribe->level) {
                case 'error':
                    if ($level !== Level::Error) {
                        return;
                    }
                    break;

                case 'warning':
                    if ( ! in_array($level, [Level::Error, Level::Warning])) {
                        return;
                    }
                    break;

                case 'info':
                    if ( ! in_array($level, [Level::Info, Level::Error, Level::Warning])) {
                        return;
                    }
                    break;

                default: return;
            }


            $recipients = explode(',', $this->config->system->log->subscribe->recipients);
            $recipients = array_map('trim', $recipients);
            $recipients = array_filter($recipients);

            if ( ! empty($recipients)) {
                if (isset($recipients['slack'])) {
                    $this->slack();
                }

                if (isset($recipients['telegram'])) {
                    $this->telegram();
                }

                if (isset($recipients['discord'])) {
                    $this->discord();
                }
            }
        }
    }
}