<?php
namespace Rowan\Classes;
use MaxMind\Db\Reader;
use Rowan\Exceptions\Exception;

/**
 * Загрузка и установка базы IP-геолокации DB-IP City Lite
 */
class Ipdb extends System {

    /**
     * Шаблон ссылки на ежемесячную базу DB-IP City Lite (подставляется YYYY-MM)
     */
    private const URL_TEMPLATE = 'https://download.db-ip.com/free/dbip-city-lite-%s.mmdb.gz';

    /**
     * Минимальный размер скачанного gz-архива, меньше — считаем ошибкой
     */
    private const MIN_ARCHIVE_SIZE = 1048576;

    /**
     * Минимальный размер распакованной базы, меньше — считаем файл битым
     */
    public const MIN_DATABASE_SIZE = 5242880;

    /**
     * Таймаут соединения при загрузке, секунд
     */
    private const CONNECT_TIMEOUT = 30;

    /**
     * Общий таймаут загрузки, секунд
     */
    private const DOWNLOAD_TIMEOUT = 900;

    /**
     * Расширение архива
     */
    private const ARCHIVE_EXTENSION = 'gz';

    /**
     * Расширение файла базы
     */
    private const DATABASE_EXTENSION = 'mmdb';

    /**
     * Имя файла базы по умолчанию
     */
    private const DEFAULT_FILE = 'ip.mmdb';


    /**
     * Загрузка актуальной базы IP-геолокации DB-IP City Lite.
     *
     * Ссылка на базу содержит месяц выпуска (YYYY-MM), поэтому месяц
     * вычисляется динамически. Если база за текущий месяц ещё не опубликована
     * (обновление выходит обычно 1-го числа), используется предыдущий месяц.
     *
     * Путь к файлу задаётся настройкой system.ipdb.file, а при её отсутствии
     * база сохраняется в system.root с именем ip.mmdb.
     *
     * @param string $destination
     * @return string
     * @throws Exception
     * @throws \DateMalformedStringException
     * @throws \Throwable
     */
    public function download(string $destination): string {

        $destination_dir = dirname($destination);
        $tmp_dir         = $this->resolveTempDir();

        if ( ! function_exists('curl_init')) {
            throw new Exception($this->_('Для загрузки базы IP-геолокации требуется расширение ext-curl.'));
        }

        if ( ! is_dir($destination_dir)) {
            throw new Exception($this->_('Папка %s не существует.', [$destination_dir]));
        }
        if ( ! is_writable($destination_dir)) {
            throw new Exception($this->_('Папка %s недоступна для записи.', [$destination_dir]));
        }

        $archive       = null;
        $archive_month = null;
        $now           = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        // Текущий месяц и предыдущий — на случай, если свежий выпуск ещё не вышел
        foreach ([$now, $now->modify('-1 month')] as $date) {
            $month = $date->format('Y-m');
            $url   = sprintf(self::URL_TEMPLATE, $month);

            echo $this->_('Загрузка DB-IP City Lite за %s: %s', [$month, $url]) . PHP_EOL;

            $tmp_archive = $this->makeTempPath($tmp_dir, self::ARCHIVE_EXTENSION);

            if ( ! $this->downloadArchive($url, $tmp_archive)) {
                echo $this->_('База за %s ещё не опубликована, пробуем предыдущий месяц.', [$month]) . PHP_EOL;
                continue;
            }

            $archive       = $tmp_archive;
            $archive_month = $month;
            break;
        }

        if ($archive === null) {
            throw new Exception($this->_('Не удалось найти доступную версию базы DB-IP.'));
        }

        $tmp_database = $this->makeTempPath($tmp_dir, self::DATABASE_EXTENSION);

        echo $this->_('Распаковка архива...') . PHP_EOL;
        try {
            $this->decompress($archive, $tmp_database);
        } catch (\Throwable $e) {
            @unlink($archive);
            @unlink($tmp_database);
            throw $e instanceof Exception ? $e : new Exception($e->getMessage());
        }

        @unlink($archive);

        $this->validate($tmp_database);

        echo $this->_('Установка базы...') . PHP_EOL;
        $this->install($tmp_database, $destination);

        clearstatcache(true, $destination);

        return $this->_('База IP-геолокации DB-IP City Lite за %s установлена: %s (%s)', [
            $archive_month,
            $destination,
            $this->formatBytes((int)filesize($destination)),
        ]);
    }


    /**
     * Определение пути к файлу базы IP-геолокации
     * @return string
     */
    public function resolveDestination(): string {

        // Имя файла задаётся настройкой system.ipdb.file, иначе используется ip.mmdb
        $file_name = $this->config?->system?->ipdb?->file;

        if (empty($file_name) || ! is_string($file_name)) {
            $file_name = self::DEFAULT_FILE;
        }

        $file_name = trim($file_name);

        // Абсолютный путь в настройке используется как есть
        if (preg_match('~^(?:[a-zA-Z]:[\\\\/]|[\\\\/])~', $file_name)) {
            return $this->normalizePath($file_name);
        }

        // Папка размещения базы берётся из system.root
        $root = trim((string)$this->config?->system?->root);

        if ($root === '') {
            return '';
        }

        $destination = rtrim(str_replace('\\', '/', $root), '/') . '/' . ltrim(str_replace('\\', '/', $file_name), '/');

        return $this->normalizePath($destination);
    }


    /**
     * Чтение информации об IP из установленной базы.
     *
     * Путь к базе определяется тем же способом, что и при загрузке.
     * Если файла нет или IP отсутствует в базе, возвращается null.
     *
     * @param string $ip
     * @return array|null Сырые данные базы или null
     * @throws \Exception
     */
    public function getIpInfo(string $ip): ?array {

        $file_path = $this->resolveDestination();

        if ($file_path === '' || ! is_file($file_path)) {
            return null;
        }

        $reader = null;

        try {
            $reader  = new Reader($file_path);
            $ip_info = $reader->get($ip);

        } catch (\Throwable $e) {
            return null;

        } finally {
            $reader?->close();
        }

        return is_array($ip_info) ? $ip_info : null;
    }


    /**
     * Человекочитаемый размер
     * @param int $bytes
     * @return string
     */
    public function formatBytes(int $bytes): string {

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? (int)floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        return round($bytes / (1024 ** $power), 2) . ' ' . $units[$power];
    }


    /**
     * Приведение пути к прямому виду без элементов "." и ".."
     * @param string $path
     * @return string
     */
    private function normalizePath(string $path): string {

        $real_path = realpath($path);

        if ($real_path !== false) {
            return $real_path;
        }

        $real_dir = realpath(dirname($path));

        if ($real_dir !== false) {
            return rtrim($real_dir, '/\\') . DIRECTORY_SEPARATOR . basename($path);
        }

        return $path;
    }


    /**
     * Определение папки для временных файлов (system.tmp)
     * @return string
     * @throws Exception
     */
    private function resolveTempDir(): string {

        $tmp_dir = trim((string)$this->config?->system?->tmp);

        if ($tmp_dir === '') {
            $tmp_dir = sys_get_temp_dir();
        }

        if (is_dir($tmp_dir) && is_writable($tmp_dir)) {
            return $tmp_dir;
        }

        $fallback = sys_get_temp_dir();

        if ($fallback !== $tmp_dir && is_dir($fallback) && is_writable($fallback)) {
            return $fallback;
        }

        throw new Exception($this->_('Папка для временных файлов %s недоступна для записи.', [$tmp_dir]));
    }


    /**
     * Формирование пути для временного файла в целевой папке
     * @param string $directory
     * @param string $extension
     * @return string
     */
    private function makeTempPath(string $directory, string $extension): string {

        return rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . '.dbip-' . bin2hex(random_bytes(8)) . '.' . $extension;
    }


    /**
     * Скачивание архива базы.
     *
     * @param string $url
     * @param string $target
     * @return bool true — архив скачан, false — файла нет (HTTP 404), можно пробовать другую версию
     * @throws Exception
     */
    private function downloadArchive(string $url, string $target): bool {

        $handle = @fopen($target, 'wb');

        if ( ! $handle) {
            throw new Exception($this->_('Не удалось открыть для записи файл %s.', [$target]));
        }

        $ch = curl_init($url);

        if ($ch === false) {
            fclose($handle);
            @unlink($target);
            throw new Exception($this->_('Не удалось инициализировать cURL.'));
        }

        curl_setopt_array($ch, [
            CURLOPT_FILE           => $handle,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT        => self::DOWNLOAD_TIMEOUT,
            CURLOPT_USERAGENT      => 'Rowan IPDB Updater',
        ]);

        $result    = curl_exec($ch);
        $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error     = curl_error($ch);
        $errno     = curl_errno($ch);

        fclose($handle);

        if ($result === false || $errno !== 0) {
            @unlink($target);
            throw new Exception($this->_('Ошибка загрузки %s: %s', [$url, $error ?: $this->_('неизвестная ошибка cURL')]));
        }

        if ($http_code === 404) {
            @unlink($target);
            return false;
        }

        if ($http_code !== 200) {
            @unlink($target);
            throw new Exception($this->_('Сервер вернул HTTP %d для %s.', [$http_code, $url]));
        }

        $size = (int)filesize($target);

        if ($size < self::MIN_ARCHIVE_SIZE) {
            @unlink($target);
            throw new Exception($this->_('Загруженный файл слишком мал (%s), вероятно вместо архива получена страница ошибки.', [$this->formatBytes($size)]));
        }

        return true;
    }


    /**
     * Распаковка gz-архива в файл базы
     * @param string $source
     * @param string $target
     * @return void
     * @throws Exception
     */
    private function decompress(string $source, string $target): void {

        if ( ! function_exists('gzopen')) {
            throw new Exception($this->_('Для распаковки базы требуется расширение ext-zlib.'));
        }

        $in = @gzopen($source, 'rb');

        if ( ! $in) {
            throw new Exception($this->_('Не удалось открыть архив %s.', [$source]));
        }

        $out = @fopen($target, 'wb');

        if ( ! $out) {
            gzclose($in);
            throw new Exception($this->_('Не удалось открыть для записи файл %s.', [$target]));
        }

        $written = 0;

        while ( ! gzeof($in)) {
            $chunk = gzread($in, 1048576);

            if ($chunk === false) {
                gzclose($in);
                fclose($out);
                @unlink($target);
                throw new Exception($this->_('Ошибка распаковки архива базы.'));
            }

            $written += fwrite($out, $chunk);
        }

        gzclose($in);
        fclose($out);

        if ($written < self::MIN_DATABASE_SIZE) {
            @unlink($target);
            throw new Exception($this->_('Распакованная база слишком мала (%s), архив повреждён.', [$this->formatBytes($written)]));
        }
    }


    /**
     * Проверка корректности файла базы
     * @param string $path
     * @return void
     * @throws Exception
     */
    private function validate(string $path): void {

        if ( ! is_file($path)) {
            throw new Exception($this->_('Файл базы %s не найден.', [$path]));
        }

        if ((int)filesize($path) < self::MIN_DATABASE_SIZE) {
            @unlink($path);
            throw new Exception($this->_('Файл базы повреждён: слишком маленький размер.'));
        }

        // Если библиотека geoip2 доступна, дополнительно проверяем структуру базы
        if (class_exists(\GeoIP2\Database\Reader::class)) {
            try {
                new \GeoIP2\Database\Reader($path);
            } catch (\Throwable $e) {
                @unlink($path);
                throw new Exception($this->_('Файл базы повреждён: %s', [$e->getMessage()]));
            }
        }
    }


    /**
     * Установка базы с резервной копией предыдущего файла
     * @param string $source
     * @param string $destination
     * @return void
     * @throws Exception
     */
    private function install(string $source, string $destination): void {

        $backup  = $destination . '.bak';
        $had_old = is_file($destination);

        if ($had_old) {
            if (is_file($backup)) {
                @unlink($backup);
            }

            if ( ! @rename($destination, $backup)) {
                @unlink($source);
                throw new Exception($this->_('Не удалось создать резервную копию %s.', [$destination]));
            }
        }

        if ( ! @rename($source, $destination)) {
            // Разные файловые системы — копируем и удаляем исходный файл
            if ( ! @copy($source, $destination)) {
                if ($had_old) {
                    @rename($backup, $destination);
                }
                @unlink($source);
                throw new Exception($this->_('Не удалось сохранить базу в %s.', [$destination]));
            }

            @unlink($source);
        }

        if ($had_old) {
            @unlink($backup);
        }
    }
}
