<?php
namespace Core3\Mod\Admin\Classes\Monitoring;
use Core3\Classes;
use Core3\Classes\Request;


/**
 *
 */
class Handler extends Classes\Handler {

    private string $base_url = "admin/monitoring";


    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getMonitoring(Request $request): array {

        $panel = new \CoreUI\Panel();
        $panel->setContent($this->getLogs($request));

        return $panel->toArray();
    }


    /**
     * @param Request $request
     * @return array
     */
    public function getCharts(Request $request): array {

        return [];
    }


    /**
     * @param Request $request
     * @param string|null $file_hash
     * @return array
     * @throws \Core3\Exceptions\DbException
     */
    public function getLogs(Request $request, string $file_hash = null): array {

        $view = new View();

        $layout = new \CoreUI\Layout();
        $layout->setJustify($layout::JUSTIFY_START);
        $layout->setDirection($layout::DIRECTION_ROW);
        $layout->setGap(15);
        $layout->setWrap($layout::NOWRAP);
        $layout->addItems()->setWidthMin(300)->setWidth(300)->setContent($view->getTableFiles($file_hash));
        $layout->addItems()->setFill(true)->setContent([
            $view->getChartFile($file_hash),
            $view->getTableLog($file_hash)
        ]);


        $content = [];
        $content[] = $this->getJsModule('admin', 'assets/monitoring/js/admin.monitoring.js');
        $content[] = $layout->toArray();

        return $content;
    }


    /**
     * @param Request $request
     * @return array
     */
    public function getRecordsLog(Request $request): array {

        // TODO 111111111
        return [];
    }


    /**
     * @param Request $request
     * @param string $file_hash
     * @return void
     */
    public function downloadLog(Request $request, string $file_hash): void {

        // TODO 111111111
        $file_path = '';
        $file_name = '';

        header("Content-Type: text/plain");
        header("Content-Length: " . filesize($file_path));
        header("Content-Disposition: attachment; filename=\"{$file_name}");

        readfile($file_path);
    }



    /**
     * Получаем логи
     * @param string $type
     * @param string $search
     * @param int    $limit_lines
     * @return array
     */
    private function getLogsData($type, $search, $limit_lines = null) {

        if (!is_file($this->config->log->access->file)) {
            throw new \Exception("Файл журнала не найден");
        }
        $handle = fopen($this->config->log->access->file, "r");
        $count_lines = 0;
        while (!feof($handle)) {
            fgets($handle, 4096);
            $count_lines += 1;
        }

        if ($search) {
            $search = preg_quote($search, '/');
        }
        rewind($handle); //перемещаем указатель в начало файла
        $body = array();
        while (!feof($handle)) {
            $tmp = fgets($handle, 4096);
            if ($search) {
                if (preg_match("/$search/", $tmp)) {
                    if (!$limit_lines || $limit_lines > count($body)) {
                        $body[] = $tmp;
                    } else {
                        array_shift($body);
                        $body[] = $tmp;
                    }
                }
            } else {
                if (!$limit_lines || $limit_lines >= count($body)) {
                    $body[] = $tmp;
                } else {
                    array_shift($body);
                    $body[] = $tmp;
                }
            }
        }
        fclose($handle);
        return array(
            'body' => implode('', $body),
            'count_lines' => $count_lines
        );
    }
}