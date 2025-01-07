<?php
namespace Core3\Mod\Admin\Classes\Logs;
use Core3\Classes\Common;
use Core3\Classes\Tools;
use Core3\Exceptions\Exception;


/**
 *
 */
class Log extends Common {

    /**
     * @return array
     */
    public function getFiles(): array {

        $files = [];
        $key   = 'log_files';

        if ($this->hasStaticCache($key)) {
            $files = $this->getStaticCache($key);

        } else {
            $log_dir = $this->config?->system?->log?->dir;

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
                            $hash = abs(crc32($dir_file));

                            $files[$hash] = [
                                'hash'        => $hash,
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

            $this->setStaticCache($key, $files);
        }

        return $files;
    }


    /**
     * Получаем логи
     * @param string $file_path
     * @param int    $offset_lines
     * @param int    $count_lines
     * @param array  $search
     * @return array
     * @throws Exception
     */
    public function getLogsData(string $file_path, int $offset_lines = 0, int $count_lines = 25, array $search = []): array {

        if ( ! is_file($file_path)) {
            throw new Exception("Файл журнала не найден");
        }

        $total_records  = 0;
        $records_offset = 0;
        $records        = [];

        $file = new ReverseFile($file_path);

        foreach ($file as $line) {
            if ($line == '') {
                continue;
            }

            if (empty($search)) {
                $total_records++;

                if ($count_lines < 0 || $count_lines <= count($records)) {
                    continue;
                }

                if ($offset_lines > 0 && $records_offset < $offset_lines) {
                    $records_offset++;
                    continue;
                }

                $record = $this->parseMonologLine($line);

                if ($record) {
                    $records[] = $record;

                } else {
                    $records[] = [
                        'message' => $line
                    ];
                }

            } else {
                $record_monolog = $this->parseMonologLine($line);
                $record         = null;

                if ($record_monolog) {
                    if ($this->searchLine($record_monolog, $search)) {
                        $record = $record_monolog;
                    }

                } else {
                    if ( ! empty($search['text']) && mb_strpos($line, $search['text']) !== false) {
                        $record = [
                            'message' => $line
                        ];
                    }
                }

                if ( ! empty($record)) {
                    $total_records++;
                }

                if ($count_lines < 0 || $count_lines <= count($records)) {
                    continue;
                }

                if ($offset_lines > 0 && $records_offset < $offset_lines) {
                    $records_offset++;
                    continue;
                }

                if ($record) {
                    $records[] = $record;
                }
            }
        }

        $file->close();


//        $file          = fopen($file_path, "r");
//        rewind($file
//
//        // TODO Чтение файла с конца
//
//        while ( ! feof($file)) {
//            $line = fgets($file);
//
//            if (empty($search)) {
//                $total_records++;
//
//                if ($count_lines < 0 || $count_lines <= count($records)) {
//                    continue;
//                }
//
//                if ($offset_lines > 0 && $records_offset < $offset_lines) {
//                    $records_offset++;
//                    continue;
//                }
//
//                if ($line == '') {
//                    $records[] = [];
//                    continue;
//                }
//
//                $record = $this->parseMonologLine($line);
//
//                if ($record) {
//                    $records[] = $record;
//
//                } else {
//                    $records[] = [
//                        'message' => $line
//                    ];
//                }
//
//            } else {
//                $record_monolog = $this->parseMonologLine($line);
//                $record         = null;
//
//                if ($record_monolog) {
//                    if ($this->searchLine($record_monolog, $search)) {
//                        $record = $record_monolog;
//                    }
//
//                } else {
//                    if ( ! empty($search['text']) && mb_strpos($line, $search['text']) !== false) {
//                        $record = [
//                            'message' => $line
//                        ];
//                    }
//                }
//
//                if ( ! empty($record)) {
//                    $total_records++;
//                }
//
//                if ($count_lines < 0 || $count_lines <= count($records)) {
//                    continue;
//                }
//
//                if ($offset_lines > 0 && $records_offset < $offset_lines) {
//                    $records_offset++;
//                    continue;
//                }
//
//                if ($record) {
//                    $records[] = $record;
//                }
//            }
//        }
//
//        fclose($file);

        return [
            'records'       => $records,
            'total_records' => $total_records,
        ];
    }


    /**
     * @param array $record
     * @param array $search
     * @return bool
     */
    private function searchLine(array $record, array $search): bool {

        $is_found = false;

        if ( ! empty($search['level'])) {
            if ( ! empty($record['level']) &&
                is_array($search['level']) &&
                in_array(mb_strtolower($record['level']), $search['level'])
            ) {
                $is_found = true;
            } else {
                return false;
            }
        }


        if ( ! empty($search['datetime']) && ! empty($record['datetime'])) {
            if ( ! empty($search['datetime']['start']) && ! empty($search['datetime']['end'])) {
                if ($record['datetime'] >= $search['datetime']['start'] && $record['datetime'] <= $search['datetime']['end']) {
                    $is_found = true;
                } else {
                    return false;
                }

            } elseif ( ! empty($search['datetime']['start'])) {
                if ($record['datetime'] >= $search['datetime']['start']) {
                    $is_found = true;
                } else {
                    return false;
                }

            } elseif ( ! empty($search['datetime']['end'])) {
                if ($record['datetime'] <= $search['datetime']['end']) {
                    $is_found = true;
                } else {
                    return false;
                }
            }
        }


        if ( ! empty($search['text'])) {
            if (
                ( ! empty($record['message']) && mb_strpos($record['message'], $search['text']) !== false) ||
                ( ! empty($record['context']) && mb_strpos($record['context'], $search['text']) !== false)
            ) {
                $is_found = true;
            } else {
                return false;
            }
        }

        return $is_found;
    }


    /**
     * @param string $line
     * @return array|null
     */
    private function parseMonologLine(string $line):? array {

        preg_match('~\[(?P<date>.*?)\]\s(?<channel>[\w\.]*)\.(?<level>.*?):\s(?<message>.*?)\s(?<context>(?:\[|\{).*(?:\]|\}))\s(?<extra>(?:\[|).*(?:\]))~', $line, $matches);

        if (empty($matches)) {
            return null;
        }

        return [
            'datetime' => substr($matches['date'], 0, 19),
            'channel'  => $matches['channel'],
            'level'    => $matches['level'],
            'message'  => $matches['message'],
            'context'  => $matches['context'],
            'extra'    => $matches['extra'],
        ];
    }
}