<?php
namespace Core3\Mod\Admin\Classes\Logs;
use Core3\Classes;
use Core3\Classes\Request;
use Core3\Exceptions\Exception;
use Core3\Exceptions\HttpException;
use CoreUI\Table;
use CoreUI\Table\Adapters\Data\Search;


/**
 *
 */
class Handler extends Classes\Handler {

    private string $base_url = "admin/logs";


    /**
     * @param Request     $request
     * @param string|null $file_hash
     * @return array
     * @throws \Core3\Exceptions\DbException
     * @throws \Exception
     */
    public function getPanelLogs(Request $request, string $file_hash = null): array {

        if (empty($file_hash)) {
            $files = (new Log())->getFiles();

            if ($files) {
                $file_hash = current($files)['hash'];
            }
        }

        $view = new View();

        $layout = new \CoreUI\Layout();
        $layout->setJustify($layout::JUSTIFY_START);
        $layout->setDirection($layout::DIRECTION_ROW);
        $layout->setGap(15);
        $layout->setWrap($layout::NOWRAP);

        $layout->addItems()->setWidthMin(300)->setWidth(300)
            ->setContent($view->getTableFiles($file_hash));

        $layout->addItems()->setFill(true)->setContent([
            $view->getChartFile($file_hash),
            $view->getTableLog($file_hash)
        ]);


        $content = [];
        $content[] = $this->getCssModule('admin', 'assets/logs/css/admin.logs.css');
        $content[] = $this->getJsModule('admin', 'assets/logs/js/admin.logs.js');
        $content[] = $layout->toArray();


        $panel = $this->getPanel();
        $panel->setContent($content);

        return $panel->toArray();
    }


    /**
     * @param Request     $request
     * @param string|null $file_hash
     * @return array
     * @throws Exception
     */
    public function getChartLogs(Request $request, string $file_hash = null): array {

        if (empty($file_hash)) {
            return [];
        }

        $log = new Log();
        $files = $log->getFiles();

        if (empty($files[$file_hash])) {
            return [];
        }


        $date_start = new \DateTime();
        $date_start->modify('-30 days');

        $log_data = $log->getLogsData($files[$file_hash]['path'], 0, 100000);
        $date_min = $date_start->format('Y-m-d');
        $datasets = [];

        foreach ($log_data['records'] as $record) {

            if (empty($record['level']) || empty($record['datetime'])) {
                continue;
            }

            if (empty($datasets[$record['level']])) {
                $datasets[$record['level']] = [
                    'data' => [],
                    'name' => ucfirst(strtolower($record['level'])),
                    'type' => 'bar',
                    'style' => [
                        'color' => match (strtolower($record['level'])) {
                            'info'    => '#0dcaf0',
                            'warning' => '#ffc107',
                            'error'   => '#dc3545',
                            'debug'   => '#212529',
                        },
                    ],
                ];
            }

            $date = substr($record['datetime'], 0, 10);

            if (empty($datasets[$record['level']]['data'][$date])) {
                $datasets[$record['level']]['data'][$date] = [
                    strtotime($date) * 1000,
                    1
                ];

            } else {
                $datasets[$record['level']]['data'][$date][1]++;
            }
        }


        for ($i = 0; $i < 30; $i++) {
            $date_format = $date_start->format('Y-m-d');

            foreach ($datasets as $key => $dataset) {
                if (empty($datasets[$key]['data'][$date_format])) {
                    $datasets[$key]['data'][$date_format] = [$date_start->getTimestamp() * 1000, 0];
                }
            }

            $date_start->modify('+1 day');
        }



        foreach ($datasets as $key => $dataset) {
            ksort($dataset['data']);
            $datasets[$key]['data'] = array_values($dataset['data']);
        }

        return [
            'datasets' => array_values($datasets),
        ];
    }


    /**
     * @param Request     $request
     * @param string|null $file_hash
     * @return array
     * @throws Exception
     */
    public function getRecordsLog(Request $request, string $file_hash = null): array {

        if (empty($file_hash)) {
            return [];
        }

        $log = new Log();
        $files = $log->getFiles();

        if (empty($files[$file_hash])) {
            return [];
        }

        $page_count = $request->getQuery('count') ?? 25;
        $page_count = is_numeric($page_count) ? max($page_count, 1) : 25;

        $page         = $request->getQuery('page') ?? 1;
        $page         = is_numeric($page) ? max($page, 1) : 1;
        $offset_count = $page > 1 ? ($page - 1) * $page_count : 0;

        $search = $request->getQuery('search');
        $search = is_array($search) ? $search : [];

        $log_data = $log->getLogsData($files[$file_hash]['path'], $offset_count, $page_count, $search);

        foreach ($log_data['records'] as $key => $record) {
            $context = $record['context'] ?? '';
            $extra   = $record['extra'] ?? '';

            if ($context) {
                $context = "<span class=\"text-warning-emphasis\">{$context}</span>";
            }

            if ($extra) {
                $extra = "<span class=\"text-info-emphasis\">{$extra}</span>";
            }

            $log_data['records'][$key]['description'] = trim("{$record['message']} {$context} {$extra}");

            $level      = strtolower($record['level'] ?? '');
            $level_type = match ($level) {
                'info'    => 'info',
                'warning' => 'warning',
                'error'   => 'danger',
                'debug'   => 'secondary',
                default   => ''
            };

            $log_data['records'][$key]['level'] = [
                'type' => $level_type,
                'text' => $level ?: '-',
            ];

            $log_data['records'][$key]['datetime'] = $record['datetime'] ?? '';
        }

        return [
            'records' => $log_data['records'],
            'total'   => $log_data['total_records'],
        ];
    }


    /**
     * @param Request $request
     * @param string  $file_hash
     * @return Classes\Response
     * @throws HttpException
     * @throws Exception
     */
    public function downloadLog(Request $request, string $file_hash): Classes\Response {

        $log = new Log();
        $files = $log->getFiles();

        if (empty($files[$file_hash])) {
            throw new HttpException(404, $this->_('Указанный файл не найден'));
        }

        $file_path = $files[$file_hash]['path'];
        $file_name = $files[$file_hash]['name'];
        $file_size = $files[$file_hash]['size'];


        return $this->getResponse()
            ->setHeaders([
                'Content-Type'        => 'text/plain',
                'Content-Length'      => $file_size,
                'Content-Disposition' => "attachment; filename=\"{$file_name}\"",
            ])
            ->setContentFile($file_path);
    }


    /**
     * @return \CoreUI\Panel
     */
    private function getPanel(): \CoreUI\Panel {

        $panel = new \CoreUI\Panel();
        $panel->setContentFit($panel::FIT);

        return $panel;
    }
}