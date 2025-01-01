<?php
namespace Core3\Mod\Admin\Classes\Monitoring;
use Core3\Classes\Common;


/**
 *
 */
class Log extends Common {


    /**
     * @return array
     */
    public function getFiles(): array {

        $log_dir = $this->config?->system?->log?->dir;
        $files   = [];

        if ($log_dir && is_dir($log_dir) && is_readable($log_dir)) {

            $dir_files = scandir($log_dir, SCANDIR_SORT_ASCENDING);

            if ($dir_files) {
                foreach ($dir_files as $dir_file) {

                    if (in_array($dir_file, [".", ".."])) {
                        continue;
                    }

                    $file_path = "{$log_dir}/{$dir_file}";

                    if (str_ends_with($dir_file, '.log') &&
                        is_file($file_path) &&
                        is_readable($file_path)
                    ) {
                        $id = crc32($dir_file);

                        $files[$id] = [
                            'id'          => $id,
                            'name'        => $dir_file,
                            'path'        => $file_path,
                            'size'        => filesize($file_path),
                            'date_modify' => filemtime($file_path),
                            'is_writable' => is_writable($file_path),
                        ];
                    }
                }
            }
        }

        return $files;
    }
}