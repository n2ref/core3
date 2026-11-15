<?php
namespace Rowan\Admin;
use Rowan\Classes\Common;


/**
 *
 */
class Cli extends Common {

    /**
     * Очистка загруженных файлов из временной директории
     * @param int $days За какое количество дней очищать файлы
     * @return void
     */
    public function clearUploadFiles(int $days = 1): void {

    }


    /**
     * Очистка старых данных в таблице core_controls
     * @param int $days За какое количество дней очищать
     * @return void
     */
    public function clearOldControls(int $days = 30): void {

        $this->modAdmin->tableControls->deleteOld($days);
    }


    /**
     * Архивирование ротированных файлов логов
     * @return void
     */
    public function archiveLogs(): void {

        $dir = $this->config?->system?->log?->dir;

        if ( ! $dir || ! is_string($dir) || ! is_dir($dir) || ! is_readable($dir)) {
            return;
        }

        $max_files = $this->config?->system?->log?->rotate?->max_files;

        $max_files = is_numeric($max_files) && $max_files >= 0
            ? (int)$max_files
            : 2;

        $dir   = rtrim($dir, '/\\');
        $files = scandir($dir);

        if ( ! $files) {
            return;
        }

        foreach ($files as $file) {
            if ( ! preg_match('/^(.+)-(\d{4}(?:-\d{2})?(?:-\d{2})?)\.log$/i', $file, $match)) {
                continue;
            }

            $source = "{$dir}/{$file}";

            if ( ! is_file($source) || ! is_readable($source)) {
                continue;
            }

            $archive = "{$dir}/{$match[1]}-{$match[2]}.zip";
            $zip     = new \ZipArchive();

            if ($zip->open($archive, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                continue;
            }

            if ( ! $zip->addFile($source, $file)) {
                $zip->close();
                unlink($archive);
                continue;
            }

            if ( ! $zip->close()) {
                continue;
            }

            if (is_file($archive)) {
                unlink($source);
            }
        }

        $files = scandir($dir);

        if ( ! $files) {
            return;
        }

        $archives = [];

        foreach ($files as $file) {
            if ( ! preg_match('/^(.+)-(\d{4}(?:-\d{2})?(?:-\d{2})?)\.zip$/i', $file, $match)) {
                continue;
            }

            $archives[$match[1]][$match[2]] = "{$dir}/{$file}";
        }

        foreach ($archives as $items) {
            if (count($items) <= $max_files) {
                continue;
            }

            krsort($items);

            foreach (array_slice($items, $max_files, null, true) as $archive) {
                if (is_file($archive)) {
                    unlink($archive);
                }
            }
        }
    }
}