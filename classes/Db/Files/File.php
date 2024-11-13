<?php
namespace Core3\Classes\Db\Files;
use Core3\Classes\Config;
use Core3\Classes\Db\TableAbstract;
use Core3\Classes\Db\TableFiles;
use Core3\Classes\Registry;
use Core3\Exceptions\Exception;


/**
 *
 */
class File {

    /**
     * @var Config|null
     */
    protected static ?Config $config = null;


    /**
     * Удаление файла
     * @param string $path
     * @return bool
     * @throws Exception
     */
    public static function delete(string $path): bool {

        if ( ! is_file($path)) {
            return false;
        }

        if ( ! is_writable($path)) {
            throw new Exception("The file is not writable: {$path}");
        }

        return unlink($path);
    }


    /**
     * Получение файла
     * @param string $path
     * @return string|null
     */
    public static function fetch(string $path):? string {

        return file_exists($path) && is_readable($path)
            ? file_get_contents($path)
            : null;
    }


    /**
     * Сохранение файла
     * @param array      $set
     * @param TableFiles $table
     * @param string     $content
     * @return string
     * @throws Exception
     */
    public static function save(array $set, TableFiles $table, string $content): string {

        $config = self::getConfig();

        if ( ! $config &&
             ! $config?->system?->files?->file?->dir ||
             ! is_string($config->system->files->file->dir) ||
             ! is_dir($config->system->files->file->dir)
        ) {
            throw new Exception('Incorrect directory config: system.files.file.dir');
        }


        $table_name = $table->getTable();
        $dir        = "{$config->system->files->file->dir}/{$table_name}";
        $dir        = self::getFormatDir($dir);

        if ( ! is_dir($dir)) {
            $is_create = mkdir($dir, 0644, true);

            if ( ! $is_create) {
                throw new Exception("Failed to create directory: {$dir}");
            }
        }

        if ( ! is_writable($dir)) {
            throw new Exception("The directory is not writable: {$dir}");
        }

        $fields = $table->getFields();

        if (empty($set[$fields['name']])) {
            throw new Exception('File name not specified');
        }

        if ( ! is_string($set[$fields['name']])) {
            throw new Exception('Incorrect file name specified');
        }


        $file_ext  = pathinfo($set[$fields['name']], PATHINFO_EXTENSION);
        $file_hash = md5($content);

        $file_name = $file_ext
            ? "{$file_hash}." . strtolower($file_ext)
            : $file_hash;

        $path = "{$dir}/{$file_name}";

        $written_bites = file_put_contents($path, $content);

        if ($written_bites === false) {
            throw new Exception("Failed to save content to file: {$path}");
        }

        return $path;
    }


    /**
     * Получение конфига системы
     * @return Config|null
     */
    private static function getConfig():? Config {

        if (self::$config) {
            return self::$config;
        }

        if (Registry::has('config')) {
            $config = Registry::get('config');

            if ($config instanceof Config) {
                self::$config = $config;
            }
        }

        return self::$config;
    }


    /**
     * Добавление формата к пути
     * @param string $dir
     * @return string
     */
    private static function getFormatDir(string $dir): string {

        $config = self::getConfig();

        if ($config &&
            $config?->system?->files?->file?->format &&
            is_string($config->system->files->file->format)
        ) {
            $format = $config->system->files->file->format;

            if (strpos($format, '{YYYY}')) {
                $format = str_replace($format, '{YYYY}', date('Y'));
            }

            if (strpos($format, '{MM}')) {
                $format = str_replace($format, '{MM}', date('m'));
            }

            if (strpos($format, '{DD}')) {
                $format = str_replace($format, '{DD}', date('d'));
            }

            if (strpos($format, '{HH}')) {
                $format = str_replace($format, '{HH}', date('H'));
            }

            $dir = rtrim($dir, '/') . '/' . ltrim($format, '/');
        }

        return $dir;
    }
}